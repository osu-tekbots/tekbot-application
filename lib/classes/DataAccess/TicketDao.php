<?php

namespace DataAccess;

use Model\Ticket;

/**
 * Handles logic related to queries on loading/editing tickets in the database
 */

class TicketDao {
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
     * Fetches all Tickets.
     * @return an array of tickets on success, false otherwise
     */

    public function getTickets() {
        try {
            $sql = '
            SELECT * 
            FROM `labs_tickets`
            ORDER BY created ASC
            ';
            $results = $this->conn->query($sql);
            return \array_map('self::ExtractTicketFromRow', $results);
        } catch(\Exception $e) {
            $this->logger->error('Failed to get any tickets: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Fetches the ticket by Id
     * @return \Model\Ticket|array ticket info with specific Id
     */

    public function getTicketById($id) {
        try {
            $sql = '
            SELECT *
            FROM `labs_tickets`
            WHERE labs_tickets.id = :id
            ';
            
            $params = array(':id' => $id);
            $results = $this->conn->query($sql, $params);
            return self::ExtractTicketFromRow($results[0]);
            /*
            foreach ($results as $row) {
                $ticket = self::ExtractTicketFromRow($row);
            }
            return $ticket;
            */
        } catch(\Exception $e) {
            $this->logger->error('Failed to get ticket by Id: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Fetches ticket by status
     * @return \Model\Ticket|array all tickets with the same status
     */

    public function getTicketsByStatus($status) {
        try {
            $sql = '
            SELECT labs_tickets.*,labs_stations.name AS desk_number, labs_rooms.name AS room FROM `labs_tickets` 
            INNER JOIN `labs_stations` ON labs_stations.id = labs_tickets.stationid 
            INNER JOIN `labs_rooms` ON labs_rooms.id = labs_stations.roomid 
            WHERE labs_tickets.status = :status 
            ';

            $params = array(':status' => $status);
            $results = $this->conn->query($sql, $params);
            /*
            $tickets = array();
            foreach ($results as $row) {
                $tickets[] = self::ExtractTicketFromRow($row); 
            }
            return $tickets;
            */
            return \array_map('self::ExtractTicketFromRow', $results);
        } catch(\Exception $e) {
            $this->logger->error('Failed to get any tickets: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Fetches the whole ticket history of a station
     * @return \Model\Ticket|array of full table info of a single station
     */

    public function getStationHistory($station) {
        try {
            $sql = '
            SELECT *
            FROM `labs_tickets`
            WHERE labs_tickets.stationid = :station
            ';

            $params = array(':station' => $station);
            $results = $this->conn->query($sql, $params);
            return \array_map('self::ExtractTicketFromRow', $results);
        } catch (\Exception $e) {
            $this->logger->error('Failed to get any tickets: ' . $e->getMessage());
            return false;
        }
    }


    /**
     * Updates unresolved ticket to a resolved one
     * Sets status to 1; makes resolved the current date; add comment; add response;
     * 
     */

    public function resolveTicket($ticket) {
        try {
            $sql = '
            UPDATE  labs_tickets 
			SET status = 1, 
            resolved = NOW(), 
            comment = :comment, 
            response = :response
			WHERE labs_tickets.id = :id
            ';

            $params = array(':id' => $ticket->getId(),
                            ':comment' => $ticket->getComment(),
                            ':response' => $ticket->getResponse());
            $this->conn->execute($sql, $params);

            return true;
        } catch(\Exception $e) {
            $this->logger->error('Failed to resolve ticket: ' . $e->getMessage());
            return false;
        }
    }
    
    public function getTicketFilesToKeep() {
        try {
            $sql = '
                SELECT image
                FROM `labs_tickets`
                WHERE resolved >= DATE_SUB(CURDATE(), INTERVAL 2 YEAR)
            ';
           
            $results = $this->conn->query($sql);
            

            $filesToKeep = [];

            foreach ($results as $row) {
                $filesToKeep[] = $row["image"];
                
            }
            
            return $filesToKeep;

        } catch (\Exception $e) {
            $this->logger->error('Failed to get any valid image file names: ' . $e->getMessage());
            return false;
        }
    }
    public function deleteOldAndOrphanTicketFiles($validFileNames) {
        $allFilePaths = glob(__DIR__ . '/../../../uploads/tickets/*');
        try {
            foreach ($allFilePaths as $filePath) {
                $filename = basename($filePath);
                
                // if this file is NOT in the list, delete it
                if (!in_array($filename, $validFileNames, true)) {
                    
                    unlink($filePath);
                }
            }
           
            return true;
        } catch (\Exception $e) {
            
            $this->logger->error('Failed to remove files: ' . $e->getMessage());
            return false;
        }
    }
    public function deleteOldTickets() {
        try {
            $sql = '
                DELETE FROM labs_tickets
                WHERE resolved < DATE_SUB(CURDATE(), INTERVAL 2 YEAR)
            ';
            $results = $this->conn->query($sql);
            return true;
        } catch (\Exception $e) {
            $this->logger->error('Failed to remove old tickets: ' . $e->getMessage());
            return false;
        }
    }
    public function purgeOldTicketsAndFiles() {
        $validFiles = $this->getTicketFilesToKeep();
        return ($this->deleteOldAndOrphanTicketFiles($validFiles) === true 
            &&  $this->deleteOldTickets() === true);
    }
    /**
     * Escalates ticket sends email to Don to inform of escalation
     * 
     */
    public function escalateTicket($ticket) {
        try {
            $sql = '
            UPDATE labs_tickets
            SET status = 2,
            isEscalated = 1
            WHERE id = :id;
            ';

            $params = array(':id' => $ticket->getId());
            $results = $this->conn->execute($sql, $params);

            return true;
        } catch(\Exception $e) {
            $this->logger->error('Failed to resolve ticket: ' . $e->getMessage());
            return false;
        }
    }

    public function getUserEmailFromId($ticket) {
        try {
            $sql = '
            SELECT email
            FROM `labs_tickets`
            WHERE labs_tickets.id = :id
            ';
            $params = array(':id' => $ticket->getId());
            $results = $this->conn->execute($sql, $params);

            return $results;
        } catch (\Exception $e) {
            $this->logger->error('Failed to resolve ticket: ' . $e->getMessage());
            return false;
        }
    }

    public function getIssueById($ticket) {
        try {
            $sql = '
            SELECT issue
            FROM `labs_tickets`
            WHERE labs_tickets.id = :id
            ';
            $params = array(':id' => $ticket->getId());
            $results = $this->conn->execute($sql, $params);

            return $results;
        } catch (\Exception $e) {
            $this->logger->error('Failed to resolve ticket: ' . $e->getMessage());
            return false;
        }
    }

    public function addNewTicket($ticket) {
        try {
            $sql = '
            INSERT INTO labs_tickets (
                stationid,
                image,
                issue,
                email,
                status,
                created,
                isEscalated
            )
            VALUES (
                :stationid,
                :image,
                :issue,
                :email,
                :status,
                :created,
                :isEscalated
            )
            ';
            $params = array(
                ':stationid' => $ticket->getStationId(),
                ':image' => $ticket->getImage(),
                ':issue' => $ticket->getIssue(),
                ':email' => $ticket->getEmail(),
                ':status' => $ticket->getStatus(),
                ':created' => $ticket->getCreated(),
                ':isEscalated' => $ticket->getIsEscalated()
            );
            $this->conn->execute($sql, $params);
            return true;
        } catch (\Exception $e) {
            $this->logger->error('Failed to add new ticket: ' . $e->getMessage());
            return false;
        }
    }
    
    public function updateTicketResponse($ticketID, $response) {
        try {
            $sql = '
            UPDATE labs_tickets
            SET response = :response
            WHERE id = :id;
            ';
            $params = array(
                ':response' => $response,
                ':id' => $ticketID
            );
            $this->conn->execute($sql, $params);
            return true;
        } catch (\Exception $e) {
            $this->logger->error('Failed to update ticket: ' . $e->getMessage());
            return false;
        }
    }
    
    public function updateTicketComment($ticketID, $comment) {
        try {
            $sql = '
            UPDATE labs_tickets
            SET comment = :comment
            WHERE id = :id
            ';
            $params = array(
                ':comment' => $comment,
                ':id' => $ticketID
            );
            $this->conn->execute($sql, $params);
            return true;
        } catch (\Exception $e) {
            $this->logger->error('Failed to update ticket: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Creates a new Ticket object using information from the database row 
     * Database = labs_tickets
     * 
     * @param array $row is results of sql querey from which information is to be extracted
     * @return \Model\Ticket
     */

    public static function ExtractTicketFromRow($row) {
        $ticket = new Ticket($row['id']);

        if(isset($row['id'])) {
            $ticket->setId($row['id']);
        }

        if(isset($row['stationid'])) {
            $ticket->setStationId($row['stationid']);
        }

        if(isset($row['image'])) {
            $ticket->setImage($row['image']);
        }

        if(isset($row['issue'])) {
            $ticket->setIssue($row['issue']);
        }

        if(isset($row['email'])) {
            $ticket->setEmail($row['email']);
        }

        if(isset($row['comment'])) {
            $ticket->setComment($row['comment']);
        }

        if(isset($row['response'])) {
            $ticket->setResponse($row['response']);
        }

        if(isset($row['status'])) {
            $ticket->setStatus($row['status']);
        }

        if(isset($row['created'])) {
            $ticket->setCreated($row['created']);
        }

        if(isset($row['resolved'])) {
            $ticket->setResolved($row['resolved']);
        }

        if(isset($row['isEscalated'])) {
            $ticket->setIsEscalated($row['isEscalated']);
        }

        if(isset($row['escalatedComments'])) {
            $ticket->setEscalatedComments($row['escalatedComments']);
        }

        return $ticket;
    }


}

?>