<?php
// Updated 11/5/2019
namespace DataAccess;

use Model\EquipmentCheckout;
use Model\EquipmentCheckoutStatus;


/**
 * Handles all of the logic related to queries on capstone project resources in the database.
 */
class EquipmentCheckoutDao {

    /** @var DatabaseConnection */
    private $conn;

    /** @var \Util\Logger */
    private $logger;

    /**
     * Creates a new instance of the data access object for capstone project data.
     *
     * @param DatabaseConnection $connection the connection to use to communiate with the database
     * @param \Util\Logger $logger the logger to use to log details about the interactions with the database
     */
    public function __construct($connection, $logger) {
        $this->conn = $connection;
        $this->logger = $logger;
    }


    /**
     * Fetches checkouts associated with a user.
     *
     * @param string $userID the ID of the user whose projects to fetch
     * @return \Model\EquipmentCheckout[]|boolean an array of projects on success, false otherwise
     */
    public function getCheckoutsForUser($userID) {
        try {
            $sql = '
            SELECT * 
            FROM equipment_checkout, equipment_checkout_status, user, equipment_reservation, contract, user_access_level  
            WHERE checkout_status_id = equipment_checkout_status.id
                AND equipment_checkout.user_id = user.user_id 
                AND user.access_level_id = user_access_level.user_access_level_id
                AND equipment_checkout.contract_id = contract.contract_id 
                AND reservation_id = eqreservation_id
                AND equipment_checkout.user_id = :uid
            ';
            $params = array(':uid' => $userID);
            $results = $this->conn->query($sql, $params);

            $checkouts = array();
            foreach ($results as $row) {
                $checkout = self::ExtractCheckoutFromRow($row, true);
                $checkouts[] = $checkout;
            }
           
            return $checkouts;
        } catch (\Exception $e) {
            $this->logger->error("Failed to get checkouts for user '$userID': " . $e->getMessage());
            return false;
        }
    }

     /**
     * Fetches the count of checkouts for a specified user
     *
     * @param string $userID the ID of the user whose projects to fetch
     * @return int on success 
     */
    public function getCheckoutCountForUser($userID) {
        try {
            $sql = '
            SELECT COUNT(*) 
            FROM equipment_checkout, equipment_checkout_status, user, equipment_reservation, contract, user_access_level  
            WHERE checkout_status_id = equipment_checkout_status.id
                AND equipment_checkout.user_id = user.user_id 
                AND user.access_level_id = user_access_level.user_access_level_id
                AND equipment_checkout.contract_id = contract.contract_id 
                AND reservation_id = eqreservation_id
                AND equipment_checkout.user_id = :uid
            ';
            $params = array(':uid' => $userID);
            $results = $this->conn->query($sql, $params);

            foreach ($results as $row) {
                return $row['COUNT(*)'];
            }
           // $results[0] = $row;
            //return $row['COUNT(*)'];
        } catch (\Exception $e) {
            $this->logger->error("Failed to get checkout counts for user '$userID': " . $e->getMessage());
            return false;
        }
    }

    /**
     * Fetches the equipment checkout with the provided ID
     *
     * @param string $id
     * @return \Model\EquipmentCheckout|boolean the equipment on success, false otherwise
     */
    public function getCheckout($id) {
        try {
            $sql = '
            SELECT * 
            FROM equipment_checkout, equipment_reservation, user, user_access_level, contract, equipment_checkout_status
            WHERE equipment_checkout.eqcheckout_id = :id
            AND equipment_checkout.reservation_id = equipment_reservation.eqreservation_id
            AND equipment_checkout.checkout_status_id = equipment_checkout_status.id
            AND equipment_checkout.user_id = user.user_id
            AND user.access_level_id = user_access_level.user_access_level_id
            AND equipment_checkout.contract_id = contract.contract_id
            ';
            $params = array(':id' => $id);
            $results = $this->conn->query($sql, $params);
            if (\count($results) == 0) {
                return false;
            }

            $checkout = self::ExtractCheckoutFromRow($results[0]);

            return $checkout;
        } catch (\Exception $e) {
            $this->logger->error("Failed to fetch checkout with id '$id': " . $e->getMessage());
            return false;
        }
    }

    /**
     * Fetches checked out equipment for admin.
     *
     * @return \Model\EquipmentCheckout[]|boolean an array of projects on success, false otherwise
     */
    public function getCheckoutsForAdmin() {
        try {
            $sql = '
            SELECT * 
            FROM equipment_checkout, equipment_checkout_status, user, user_access_level, equipment_reservation, contract 
            WHERE equipment_checkout.user_id = user.user_id 
                AND equipment_checkout.contract_id = contract.contract_id 
                AND user.access_level_id = user_access_level.user_access_level_id
                AND equipment_checkout.checkout_status_id = equipment_checkout_status.id
                AND reservation_id = eqreservation_id

            ';
            $results = $this->conn->query($sql);

            $checkouts = array();
            foreach ($results as $row) {
                $checkout = self::ExtractCheckoutFromRow($row, true);
                $checkouts[] = $checkout;
            }
           
            return $checkouts;
        } catch (\Exception $e) {
            $this->logger->error("Failed to get admin checkouts: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Fetches checked out equipment count for admin.
     *
     * @return \Model\EquipmentCheckout[]|boolean an array of projects on success, false otherwise
     */
    public function getCheckoutCountForAdmin() {
        try {
            $sql = '
            SELECT * 
            FROM equipment_checkout, equipment_checkout_status, user, user_access_level, equipment_reservation, contract 
            WHERE equipment_checkout.user_id = user.user_id 
                AND equipment_checkout.contract_id = contract.contract_id 
                AND user.access_level_id = user_access_level.user_access_level_id
                AND equipment_checkout.checkout_status_id = equipment_checkout_status.id
                AND reservation_id = eqreservation_id

            ';
            $results = $this->conn->query($sql);

            $checkouts = array();
            foreach ($results as $row) {
                $checkout = self::ExtractCheckoutFromRow($row, true);
                $checkouts[] = $checkout;
            }
           
            return $checkouts;
        } catch (\Exception $e) {
            $this->logger->error("Failed to get admin checkouts: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Adds a new equipment checkout entry into the database.
     *
     * @param \Model\EquipmentCheckout $checkout the equipment to add
     * @return boolean true if successful, false otherwise
     */
    public function addNewCheckout($checkout) {
        try {
            $sql = '
            INSERT INTO equipment_checkout VALUES (
                :id,
                :userid,
                :reservationid,
                :equipmentid,
                :statusid,
                :contractid,
                :pickuptime,
                :returntime,
                :returndeadline,
                :notes,
                :dupdated,
                :dcreated
            )
            ';
            $params = array(
                ':id' => $checkout->getCheckoutID(),
                ':userid' => $checkout->getUserID(),
                ':reservationid' => $checkout->getReservationID(),
                ':equipmentid' => $checkout->getEquipmentID(),
                ':statusid' => $checkout->getStatusID(),
                ':contractid' => $checkout->getContractID(),
                ':pickuptime' => QueryUtils::FormatDate($checkout->getPickupTime()),
                ':returntime' => QueryUtils::FormatDate($checkout->getReturnTime()),
                ':returndeadline' => $checkout->getDeadlineTime(),
                ':notes' => $checkout->getNotes(),
                ':dupdated' => QueryUtils::FormatDate($checkout->getDateUpdated()),
                ':dcreated' => QueryUtils::FormatDate($checkout->getDateCreated())
            );
            $this->conn->execute($sql, $params);
            return true;
        } catch (\Exception $e) {
            $this->logger->error('Failed to add new equipment: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Updates an equipment entry into the database.
     *
     * @param \Model\EquipmentCheckout $checkout the equipment to add
     * @return boolean true if successful, false otherwise
     */
    public function updateCheckout($checkout) {
        try {
            $sql = '
            UPDATE equipment_checkout SET
                user_id = :userid,
                reservation_id = :reservationid,
                equipment_id = :equipmentid,
                checkout_status_id = :statusid,
                contract_id = :contractid,
                pickup_time = :pickuptime,
                return_time = :returntime,
                return_deadline = :deadlinetime,
                notes = :notes,
                date_updated = :dupdated
            WHERE eqcheckout_id = :id
            ';
            $params = array(
                ':id' => $checkout->getCheckoutID(),
                ':userid' => $checkout->getUserID(),
                ':reservationid' => $checkout->getReservationID(),
                ':equipmentid' => $checkout->getEquipmentID(),
                ':statusid' => $checkout->getStatusID(),
                ':contractid' => $checkout->getContractID(),
                ':pickuptime' => $checkout->getPickupTime(),
                ':returntime' => QueryUtils::FormatDate($checkout->getReturnTime()),
                ':deadlinetime' => $checkout->getDeadlineTime(),
                ':notes' => $checkout->getNotes(),
                ':dupdated' => QueryUtils::FormatDate($checkout->getDateUpdated())
            );
            $this->conn->execute($sql, $params);
            return true;
        } catch (\Exception $e) {
            $id = $checkout->getCheckoutID();
            $this->logger->error("Failed to update checkout with id '$id': " . $e->getMessage());
            return false;
        }
    }

    /**
     * Fetches a list of categories for checkout status
     *
     * @return \Model\EquipmentCheckoutStatus[]|boolean an array of categories on success, false otherwise
     */
    public function getCheckoutStatusTypes() {
        try {
            $sql = 'SELECT * FROM equipment_checkout_status';
            $results = $this->conn->query($sql);

            $categories = array();
            foreach ($results as $row) {
                $categories[] = self::ExtractCheckoutStatusFromRow($row);
            }

            return $categories;
        } catch (\Exception $e) {
            $this->logger->error('Failed to get checkout status: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Extracts Checkout object using information from the database row
     *
     * @param mixed[] $row the row in the database from which information is to be extracted
     * @return \Model\EquipmentCheckout
     */
    public static function ExtractCheckoutFromRow($row, $userInRow = false) {
        $checkout = new EquipmentCheckout($row['eqcheckout_id']);
        $checkout->setUserID($row['user_id']);
        $checkout->setReservationID($row['reservation_id']);
        $checkout->setEquipmentID($row['equipment_id']);
        $checkout->setStatusID(self::ExtractCheckoutStatusFromRow($row, true));
        $checkout->setContractID($row['contract_id']);
        $checkout->setPickupTime($row['pickup_time']);
        $checkout->setReturnTime($row['return_time']);
        $checkout->setDeadlineTime($row['return_deadline']);
        $checkout->setNotes($row['notes']);
        $checkout->setDateUpdated(new \DateTime($row['date_updated']));
        $checkout->setDateCreated(new \DateTime($row['date_created']));

        if ($userInRow) {
            $checkout->setUser(UsersDao::ExtractUserFromRow($row));
        }
        return $checkout;
    }

    /**
     * Creates a new checkout status enum object by extracting the necessary information from a row in a database.
     * 
     *
     * @param mixed[] $row the row from the database
     * @param boolean $userInRow flag indicating whether entries from the user table are in the row or not
     * @return \Model\CheckoutStatus the user type extracted from the row
     */
    public static function ExtractCheckoutStatusFromRow($row, $userInRow = false) {
        $id = $userInRow ? 'status_name' : 'id';
        $name = isset($row['status_name']) ? $row['status_name'] : null;
        return new EquipmentCheckoutStatus($row[$id], $name);
    }
}


