<?php

namespace DataAccess;

use Model\Task;

/**
 * Handles logic related to queries on loading/editing tickets in the database
 */

class TaskDao {
    /** @var DatabaseConnection */
    private $conn;

    /** @var \Util\Logger */
    private $logger;

    /**
     * Creates a new instance of the data access object for ticket data
    *
    * @param DatabaseConnection $conn the connection used to communicate to the database
    * @param \Util\Logger $logger the logger used to log details about the interaction with the database
    */
    public function __construct($connection, $logger) {
        $this->conn = $connection;
        $this->logger = $logger;
    }

    /**
     * Fetches all Tasks.
     * @return an array of tasks on success, false otherwise
     */

    public function getAllTasks() {
        try {
            $sql = '
            SELECT * 
            FROM `tekbots_tasks`
            ORDER BY created ASC
            ';
            $results = $this->conn->query($sql);
            return \array_map('self::ExtractTaskFromRow', $results);
        } catch(\Exception $e) {
            $this->logger->error('Failed to get any tasks: ' . $e->getMessage());
            return false;
        }
    }
	
	/**
     * Fetches all Tasks that do not have a completed date.
     * @return an array of tasks on success, false otherwise
     */

    public function getAllIncompleteTasks() {
        try {
            $sql = 'SELECT * 
                FROM `tekbots_tasks`
                WHERE completed IS NULL 
                ORDER BY is_urgent DESC, created ASC
            ';
            $results = $this->conn->query($sql);
            return \array_map('self::ExtractTaskFromRow', $results);
        } catch(\Exception $e) {
            $this->logger->error('Failed to get any incomplete tasks: ' . $e->getMessage());
            return false;
        }
    }
	
	/**
     * Fetches all Tasks that have a completed date.
     * @return an array of tasks on success, false otherwise
     */

    public function getAllCompleteTasks($startDate, $endDate) {
        try {
            if($startDate && $endDate) {
                $this->logger->info('Fetching all completed tasks between '.$startDate.' and '.$endDate);
                $sql = 'SELECT * 
                    FROM `tekbots_tasks`
                    WHERE completed IS NOT NULL 
                        AND `completed` >= :startDate
                        AND `completed` <= :endDate
                    ORDER BY completed DESC, created ASC
                ';
                $params = array(
                    ':startDate' => $startDate,
                    ':endDate' => $endDate
                );
                $results = $this->conn->query($sql, $params);
            } else if($startDate) {
                $this->logger->info('Fetching all completed tasks after '.$startDate);
                $sql = 'SELECT * 
                    FROM `tekbots_tasks`
                    WHERE completed IS NOT NULL 
                        AND `completed` >= :startDate
                    ORDER BY completed DESC, created ASC
                ';
                $params = array(
                    ':startDate' => $startDate
                );
                $results = $this->conn->query($sql, $params);
            } else if($endDate) {
                $this->logger->info('Fetching all completed tasks before '.$endDate);
                $sql = 'SELECT * 
                    FROM `tekbots_tasks`
                    WHERE completed IS NOT NULL 
                        AND `completed` <= :endDate
                    ORDER BY completed DESC, created ASC
                ';
                $params = array(
                    ':endDate' => $endDate
                );
                $results = $this->conn->query($sql, $params);
            } else {
                $this->logger->info('Fetching all completed tasks');
                $sql = 'SELECT * 
                    FROM `tekbots_tasks`
                    WHERE completed IS NOT NULL 
                    ORDER BY completed DESC, created ASC
                ';
                $results = $this->conn->query($sql);
            }
            return \array_map('self::ExtractTaskFromRow', $results);
        } catch(\Exception $e) {
            $this->logger->error('Failed to get any incomplete tasks: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Fetches the task by id
     * @param int $id Id number for the task
     * @return \Model\Task task info with specific Id
     */

    public function getTaskById($id) {
        try {
            $sql = '
            SELECT *
            FROM `tekbots_tasks`
            WHERE tekbots_tasks.id = :id
            ';
            
            $params = array(':id' => $id);
            $results = $this->conn->query($sql, $params);
            return self::ExtractTaskFromRow($results[0]);
        } catch(\Exception $e) {
            $this->logger->error('Failed to get task by Id: ' . $e->getMessage());
            return false;
        }
    }
	
	/**
     * Deletes the task by id
     * @param int $id Id number for the task
     * @return treu on success, false otherwise
     */

    public function deleteTaskById($id) {
        try {
            $sql = '
            DELETE FROM `tekbots_tasks`
            WHERE tekbots_tasks.id = :id
            ';
            
            $params = array(':id' => $id);
            $results = $this->conn->execute($sql, $params);
            return true;
        } catch(\Exception $e) {
            $this->logger->error('Failed to delete task by Id: ' . $e->getMessage());
            return false;
        }
    }

 
    /**
     * Updates a task in the tekbots_tasks table
	 * @param \Model\Task The task to be updated
     * @return true on success, false otherwise
     */

    public function updateTask($task) {
        try {
            $sql = 'UPDATE  tekbots_tasks 
                SET description = :description, 
                    created = :created, 
                    completed = :completed, 
                    creator = :creator, 
                    completer = :completer,
                    is_urgent = :urgent
                WHERE tekbots_tasks.id = :id
            ';

            $params = array(':id' => $task->getId(),
                            ':description' => $task->getDescription(),
                            ':created' => $task->getCreated()?->format('Y-m-d'),
                            ':completed' => $task->getCompleted()?->format('Y-m-d'),
                            ':creator' => $task->getCreator(),
                            ':completer' => $task->getCompleter(),
                            ':urgent' => $task->getUrgent());
            $this->conn->execute($sql, $params);

            return true;
        } catch(\Exception $e) {
            $this->logger->error('Failed to update task: ' . $e->getMessage());
            return false;
        }
    }

	/**
     * Adds a task in the tekbots_tasks table
	 * @param \Model\Task $task The task to be updated
     * @return true on success, false otherwise
     */

    public function addNewTask($task) {
        try {
            $sql = '
            INSERT INTO tekbots_tasks (
                description,
                created,
                completed,
                creator,
                completer,
                is_urgent
            )
            VALUES (
                :description,
                :created,
                :completed,
                :creator,
                :completer,
                :urgent
            )
            ';
            $params = array(':description' => $task->getDescription(),
                            ':created' => $task->getCreated()?->format('Y-m-d'),
                            ':completed' => $task->getCompleted()?->format('Y-m-d'),
                            ':creator' => $task->getCreator(),
                            ':completer' => $task->getCompleter(),
                            ':urgent' => $task->getUrgent());
            $this->conn->execute($sql, $params);
            return true;
        } catch (\Exception $e) {
            $this->logger->error('Failed to add new ticket: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Creates a new Task object using information from the database row 
     * Database = tekbots_tickets
     * 
     * @param array $row is results of sql querey from which information is to be extracted
     * @return \Model\Task
     */

    public static function ExtractTaskFromRow($row) {
        $task = new Task($row['id']);

        if(isset($row['id'])) {
            $task->setId($row['id']);
        }
		
		if(isset($row['description'])) {
            $task->setDescription($row['description']);
        }

        if(isset($row['created'])) {
            $task->setCreated(new \DateTime($row['created']));
        }

        if(isset($row['completed'])) {
            $task->setCompleted(new \DateTime($row['completed']));
        }

        if(isset($row['creator'])) {
            $task->setCreator($row['creator']);
        }

        if(isset($row['completer'])) {
            $task->setCompleter($row['completer']);
        }

        if(isset($row['is_urgent'])) {
            $task->setUrgent($row['is_urgent']);
        }

        return $task;
    }


}

?>