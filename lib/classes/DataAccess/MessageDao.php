<?php

namespace DataAccess;

use Model\Message;

/**
 * Handles all of the logic related to queries on loading/editing messages in the database.
 */
class MessageDao {

    /** @var DatabaseConnection */
    private $conn;

    /** @var \Util\Logger */
    private $logger;

    /**
     * Creates a new instance of the data access object for 3d printer data.
     *
     * @param DatabaseConnection $connection the connection to use to communiate with the database
     * @param \Util\Logger $logger the logger to use to log details about the interactions with the database
     */
    public function __construct($connection, $logger) {
        $this->conn = $connection;
        $this->logger = $logger;
    }

    /**
     * Fetches all Lockers.
     * @return an array of lockers on success, false otherwise
     */
    public function getMessages() {
        try {
            $sql = '
            SELECT * FROM `messages`
            ';
            $results = $this->conn->query($sql);

            $messages = array();
            foreach ($results as $row) {
                $message = self::ExtractMessageFromRow($row);
                $messages[] = $message;
            }
            return $messages;
        } catch (\Exception $e) {
            $this->logger->error('Failed to get any messages: ' . $e->getMessage());
            return false;
        }
    }


    /**
     * Fetches all Print Jobs by ID.
     * @return \Model\PrinterJob[]|boolean an array of printers on success, false otherwise
     */
    public function getMessageByID($id) {
        try {
            $sql = '
            SELECT * 
            FROM `messages`
            WHERE messages.message_id = :id
            ';
            $params = array(':id' => $id);
            $results = $this->conn->query($sql, $params);

            
            foreach ($results as $row) {
                $message = self::ExtractMessageFromRow($row);

            }
            return $message;
        } catch (\Exception $e) {
            $this->logger->error('Failed to get message with id '.$id.': ' . $e->getMessage());
            return false;
        }
    }
	
	 /**
     * Fetches all Print Jobs by ID.
     * @return \Model\PrinterJob[]|boolean an array of printers on success, false otherwise
     */
    public function getMessagesByTool($tool_id) {
        try {
            $sql = '
            SELECT * 
            FROM `messages`
            WHERE messages.tool_id = :tool_id
            ';
            $params = array(':tool_id' => $tool_id);
            $results = $this->conn->query($sql, $params);

            $messages = array();
            foreach ($results as $row) {
                $message = self::ExtractMessageFromRow($row);
                $messages[] = $message;
            }
            return $messages;
        } catch (\Exception $e) {
            $this->logger->error('Failed to get message with id '.$id.': ' . $e->getMessage());
            return false;
        }
    }

    public function updateMessage($message) {
        try {
            $sql = '
            UPDATE messages SET
                subject = :subject,
                body = :body,
                format = :format
            WHERE message_id = :id
            ';
            $params = array(
                ':id' => $message->getMessageId(),
                ':subject' => $message->getSubject(),
                ':body' => $message->getBody(),
                ':format' => $message->getFormat()
            );
            $this->conn->execute($sql, $params);
            return true;
        } catch (\Exception $e) {
            $this->logger->error('Failed to update message: ' . $e->getMessage());
            return false;
        }
    }

    public function deleteMessageByID($id) {
        try {
            $sql = '
            DELETE FROM messages
            WHERE message_id = :id
            ';
            $params = array(
                ':id' => $id,
            );
            $this->conn->execute($sql, $params);

            return true;
        } catch (\Exception $e) {
            $this->logger->error('Failed to remove locker from DB: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Creates a new Equipment object using information from the database row
     *
     * @param mixed[] $row the row in the database from which information is to be extracted
     * @return \Model\Equipment
     */
    public static function ExtractMessageFromRow($row) {
        $message = new Message($row['message_id']);

		if(isset($row['subject'])){
			$message->setSubject($row['subject']);
		}
		if(isset($row['body'])){
			$message->setBody($row['body']);
		}
		if(isset($row['format'])){
			$message->setFormat($row['format']);
		}
       
        return $message;
    }

   
}

?>