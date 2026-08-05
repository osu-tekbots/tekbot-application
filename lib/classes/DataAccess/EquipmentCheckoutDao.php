<?php
namespace DataAccess;

use Model\EquipmentCheckout;
use Model\EquipmentReservation;


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
     * Counts all currently-active reservations
     * 
     * @return int|false The number of active reservations, or false if an error occured
     */
    public function getReservationCountForEmployee() {
        try {
            $sql = 'SELECT COUNT(er_id) AS count FROM equipment_reservation
                    LEFT JOIN equipment_checkout ON ec_er_id = er_id
                WHERE NOT er_is_employee_dismissed AND ec_id IS NULL AND er_date_reserved >= NOW() - INTERVAL 1 HOUR;
            ';
    
            $result = $this->conn->query($sql);

            return $result[0]['count'];
        } catch (\Exception $e) {
            $this->logger->error("Failed to get reservation count: " . $e->getMessage());
            return false;
        }
    }


    /**
     * Counts all currently-active reservations for the given user
     * 
     * @return int|false The number of active reservations, or false if an error occured
     */
    public function getReservationCountForUser($userID) {
        try {
            $sql = 'SELECT COUNT(er_id) AS count FROM equipment_reservation
                    LEFT JOIN equipment_checkout ON ec_er_id = er_id
                WHERE NOT er_is_employee_dismissed AND ec_id IS NULL AND er_date_reserved >= NOW() - INTERVAL 1 HOUR
                    AND er_u_id = :user_id;
            ';
            $params = ['user_id' => $userID];
    
            $result = $this->conn->query($sql, $params);

            return $result[0]['count'];
        } catch (\Exception $e) {
            $this->logger->error("Failed to get reservation count for user: " . $e->getMessage());
            return false;
        }
    }


    /**
     * Counts all currently-active checkouts for the given user
     * 
     * @return int|false The number of active checkouts, or false if an error occured
     */
    public function getCheckoutCountForUser($userID) {
        try {
            $sql = 'SELECT COUNT(ec_id) AS count FROM equipment_checkout
                WHERE ec_date_returned IS NULL AND ec_u_id = :user_id;
            ';
            $params = ['user_id' => $userID];
    
            $result = $this->conn->query($sql, $params);

            return $result[0]['count'];
        } catch (\Exception $e) {
            $this->logger->error("Failed to get checkout count for user: " . $e->getMessage());
            return false;
        }
    }


    /**
     * Counts all overdue checkouts for the given user
     * 
     * @return int|false The number of active checkouts, or false if an error occured
     */
    public function getLateCheckoutCountForUser($userID) {
        try {
            $sql = 'SELECT COUNT(ec_id) AS count FROM equipment_checkout
                WHERE ec_date_returned IS NULL AND ec_date_due < NOW() AND ec_u_id = :user_id;
            ';
            $params = ['user_id' => $userID];
    
            $result = $this->conn->query($sql, $params);

            return $result[0]['count'];
        } catch (\Exception $e) {
            $this->logger->error("Failed to get checkout count for user: " . $e->getMessage());
            return false;
        }
    }


    /**
     * Gets all non-dismissed reservations for employees to review.
     * 
     * @return \Model\EquipmentReservation[]|boolean The active reservations, or false if an error occured
     */
    public function getEmployeeReservations() {
        try {
            $sql = 'SELECT equipment_reservation.* FROM equipment_reservation
                    LEFT JOIN equipment_checkout ON ec_er_id = er_id
                WHERE NOT er_is_employee_dismissed AND ec_id IS NULL AND er_date_reserved >= NOW() - INTERVAL 1 HOUR;
            ';
    
            $result = $this->conn->query($sql);

            $reservations = [];
            foreach ($result as $row) {
                $reservations[] = self::ExtractReservationFromRow($row);
            }

            return $reservations;
        } catch (\Exception $e) {
            $this->logger->error("Failed to get reservations: " . $e->getMessage());
            return false;
        }
    }


    /**
     * Gets all checkout history for employees to review.
     * 
     * @return \Model\EquipmentCheckout[]|boolean All checkouts, or false if an error occured
     */
    public function getEmployeeCheckouts() {
        try {
            $sql = 'SELECT * FROM equipment_checkout;';

            $results = $this->conn->query($sql);

            $checkouts = [];
            foreach ($results as $row) {
                $checkouts[] = self::ExtractCheckoutFromRow($row);
            }

            return $checkouts;
        } catch (\Exception $e) {
            $this->logger->error("Failed to get checkouts: " . $e->getMessage());
            return false;
        }
    }


    /**
     * Gets all checkouts that're overdue for email follow-up.
     * 
     * @return \Model\EquipmentCheckout[]|boolean All overdue checkouts, or false if an error occured
     */
    public function getLateCheckoutsForEmployee() {
        try {
            $sql = 'SELECT * FROM equipment_checkout
                WHERE ec_date_returned IS NULL AND ec_date_due < NOW();
            ';

            $results = $this->conn->query($sql);

            $checkouts = [];
            foreach ($results as $row) {
                $checkouts[] = self::ExtractCheckoutFromRow($row);
            }

            return $checkouts;
        } catch (\Exception $e) {
            $this->logger->error("Failed to get late checkouts: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Fetches the reservation with the given ID.
     * 
     * @param int $id The ID of the reservation to retrieve
     * 
     * @return \Model\EquipmentReservation|false The reservation, or false if an error occured
     */
    public function getReservation($id) {
        try {
            $sql = 'SELECT * FROM equipment_reservation WHERE er_id = :er_id;';
            $params = ['er_id' => $id];

            $results = $this->conn->query($sql, $params);

            return self::ExtractReservationFromRow($results[0]);
        } catch (\Exception $e) {
            $this->logger->error("Failed to get active reservation for $id: " . $e->getMessage());
            return false;
        }
    }


    /**
     * Gets the checkout with the given ID.
     * 
     * @param int $id The ID of the checkout to retrieve
     * 
     * @return \Model\EquipmentCheckout|boolean The matching checkout, or false if an error occured
     */
    public function getCheckout($id) {
        try {
            $sql = 'SELECT * FROM equipment_checkout WHERE ec_id = :ec_id;';
            $params = ['ec_id' => $id];

            $results = $this->conn->query($sql, $params);

            return self::ExtractCheckoutFromRow($results[0]);
        } catch (\Exception $e) {
            $this->logger->error("Failed to get checkouts: " . $e->getMessage());
            return false;
        }
    }


    /**
     * Fetches the active reservation for the given equipment unit
     * 
     * @param int $id The unit to fetch the active reservation for
     * 
     * @return \Model\EquipmentReservation|false The active reservation, or false if
     * there's no active reservation or an error occured
     */
    public function getActiveReservation($id) {
        try {
            $sql = 'SELECT equipment_reservation.* FROM equipment_reservation
                    LEFT JOIN equipment_checkout ON ec_er_id = er_id
                WHERE 
                    er_eu_id = :eu_id
                    AND NOT er_is_employee_dismissed
                    AND ec_id IS NULL
                    AND er_date_reserved >= NOW() - INTERVAL 1 HOUR;
            ';
            $params = ['eu_id' => $id];

            $results = $this->conn->query($sql, $params);
            if (\count($results) == 0) {
                return false;
            }

            return self::ExtractReservationFromRow($results[0]);
        } catch (\Exception $e) {
            $this->logger->error("Failed to get active reservation for $id: " . $e->getMessage());
            return false;
        }
    }


    /**
     * Fetches the active checkout for the given equipment unit
     * 
     * @param int $id The unit to fetch the active checkout for
     * 
     * @return \Model\EquipmentCheckout|false The active checkout, or false if there's no
     * active checkout or an error occured
     */
    public function getActiveCheckout($id) {
        try {
            $sql = 'SELECT * FROM equipment_checkout WHERE ec_eu_id = :eu_id AND ec_date_returned IS NULL;';
            $params = ['eu_id' => $id];

            $results = $this->conn->query($sql, $params);
            if (\count($results) == 0) {
                return false;
            }

            return self::ExtractCheckoutFromRow($results[0]);
        } catch (\Exception $e) {
            $this->logger->error("Failed to get active checkout for $id: " . $e->getMessage());
            return false;
        }
    }


    /**
     * Finds an available (not reserved/checked out) equipment unit of the given type, if
     * any are available. Only considers public, non-deleted units, making it suitable for
     * unprivileged use.
     * 
     * @return int|boolean The unit ID if one found, false otherwise
     */
    public function getAvailableUnit($typeID) {
        try {
            $sql = 'SELECT eu_id FROM EquipmentUnitStatusView
                WHERE et_id = :et_id AND eu_is_public AND NOT eu_is_deleted AND checkout_status = \'Available\' COLLATE utf8mb4_unicode_ci
                LIMIT 1
            ;';
            $params = [ 'et_id' => $typeID ];
    
            $result = $this->conn->query($sql, $params);
            
            if (empty($result)) {
                return false;
            }

            return $result[0]['eu_id'];
        } catch (\Exception $e) {
            $this->logger->error("Failed to get available unit for '$typeID': " . $e->getMessage());
            return false;
        }
    }


    /**
     * Counts available (not reserved/checked out) equipment unit of the given type. Only
     * considers public, non-deleted units, making it suitable for unprivileged use.
     * 
     * @return int|boolean The number of available units; false if an error occurs
     */
    public function countAvailableUnits($typeID) {
        try {
            $sql = 'SELECT COUNT(eu_id) AS count FROM EquipmentUnitStatusView
                WHERE et_id = :et_id AND eu_is_public AND NOT eu_is_deleted AND checkout_status = \'Available\' COLLATE utf8mb4_unicode_ci;';
            $params = [ 'et_id' => $typeID ];
    
            $result = $this->conn->query($sql, $params);

            return $result[0]['count'];
        } catch (\Exception $e) {
            $this->logger->error("Failed to count available units for '$typeID': " . $e->getMessage());
            return false;
        }
    }


    /**
     * Saves the given reservation request.
     * 
     * TODO: doesn't validate that eu_id is actually available
     * 
     * @return boolean Whether the reservation was successfully saved
     */
    public function addReservation($reservation) {
        try {
            $sql = 'INSERT INTO equipment_reservation(er_eu_id, er_u_id, er_date_reserved, er_is_employee_dismissed)
                VALUES (:eu_id, :u_id, :date_reserved, :is_employee_dismissed)';
            $params = [
                'eu_id' => $reservation->getUnitID(),
                'u_id' => $reservation->getUserID(),
                'date_reserved' => QueryUtils::formatDate($reservation->getDateReserved()),
                'is_employee_dismissed' => $reservation->getIsEmployeeDismissed(),
            ];
    
            $result = $this->conn->execute($sql, $params);

            return true;
        } catch (\Exception $e) {
            $this->logger->error('Failed to reserve unit: ' . $e->getMessage());
            return false;
        }
    }


    /**
     * Creates a new checkout record.
     * 
     * TODO: doesn't validate that eu_id is actually available
     * 
     * @return boolean Whether the checkout was successfully created
     */
    public function addCheckout($checkout) {
        try {
            $sql = 'INSERT INTO equipment_checkout(
                ec_er_id, ec_eu_id, ec_u_id, ec_date_checked_out, ec_date_due, ec_date_returned, ec_date_updated
            ) VALUES (
                :er_id, :eu_id, :u_id, :date_checked_out, :date_due, :date_returned, :date_updated
            );';
            $params = [
                'er_id' => $checkout->getReservationID(),
                'eu_id' => $checkout->getUnitID(),
                'u_id' => $checkout->getUserID(),
                'date_checked_out' => QueryUtils::formatDate($checkout->getDateCheckedOut()),
                'date_due' => QueryUtils::formatDate($checkout->getDateDue()),
                'date_returned' => QueryUtils::formatDate($checkout->getDateReturned()),
                'date_updated' => QueryUtils::formatDate($checkout->getDateUpdated()),
            ];
    
            $result = $this->conn->execute($sql, $params);

            return true;
        } catch (\Exception $e) {
            $this->logger->error('Failed to check out unit: ' . $e->getMessage());
            return false;
        }
    }


    /**
     * Dismisses a reservation, making it no longer show up in queries. Intended for
     * employee confirmation that an expired, unfulfilled reservation is no longer
     * relevant.
     * 
     * @param int $reservationID The reservation to dismiss
     * 
     * @return boolean Whether the dismissal succeeded
     */
    public function dismissReservation($reservationID) {
        try {
            $sql = 'UPDATE equipment_reservation
                SET er_is_employee_dismissed = TRUE
                WHERE er_id = :er_id;
            ';
            $params = ['er_id' => $reservationID];

            $this->conn->execute($sql, $params);

            return true;
        } catch (\Exception $e) {
            $this->logger->error("Failed to dismiss reservation '$reservationID': " . $e->getMessage());
            return false;
        }
    }


    /**
     * Updates the checkout record with the matching checkoutID.
     * 
     * @param $checkout The checkout to update
     * 
     * @return boolean Whether the checkout was successfully updated
     */
    public function updateCheckout($checkout) {
        try {
            $sql = 'UPDATE equipment_checkout
                SET ec_er_id = :er_id,
                    ec_eu_id = :eu_id,
                    ec_u_id = :u_id,
                    ec_date_due = :date_due,
                    ec_date_returned = :date_returned,
                    ec_date_updated = :date_updated
                WHERE ec_id = :ec_id;
            ';
            $params = [
                'er_id' => $checkout->getReservationID(),
                'eu_id' => $checkout->getUnitID(),
                'u_id' => $checkout->getUserID(),
                'date_due' => QueryUtils::formatDate($checkout->getDateDue()),
                'date_returned' => QueryUtils::formatDate($checkout->getDateReturned()),
                'date_updated' => QueryUtils::formatDate($checkout->getDateUpdated()),
                'ec_id' => $checkout->getCheckoutID()
            ];
    
            $this->conn->execute($sql, $params);
    
            return true;
        } catch (\Exception $e) {
            $this->logger->error("Failed to update checkout: " . $e->getMessage());
            return false;
        }
    }


    /**
     * Extracts Reservation object using information from the database row
     *
     * @param mixed[] $row the row in the database from which information is to be extracted
     * @return \Model\EquipmentReservation
     */
    public static function ExtractReservationFromRow($row, $userInRow = false) {
        $reservation = new EquipmentReservation($row['er_id']);
        $reservation->setUnitID($row['er_eu_id']);
        $reservation->setUserID($row['er_u_id']);
        $reservation->setDateReserved(new \DateTime($row['er_date_reserved'] ?? 'now'));
        $reservation->setIsEmployeeDismissed($row['er_is_employee_dismissed']);

        return $reservation;
    }


    /**
     * Extracts Checkout object using information from the database row
     *
     * @param mixed[] $row the row in the database from which information is to be extracted
     * @return \Model\EquipmentCheckout
     */
    public static function ExtractCheckoutFromRow($row, $userInRow = false) {
        $checkout = new EquipmentCheckout($row['ec_id']);
        $checkout->setReservationID($row['ec_er_id']);
        $checkout->setUnitID($row['ec_eu_id']);
        $checkout->setUserID($row['ec_u_id']);
        $checkout->setDateCheckedOut(new \DateTime($row['ec_date_checked_out'] ?? 'now'));
        $checkout->setDateDue(new \DateTime($row['ec_date_due'] ?? 'now'));
        $checkout->setDateReturned($row['ec_date_returned'] ? new \DateTime($row['ec_date_returned']) : null);
        $checkout->setDateUpdated(new \DateTime($row['ec_date_updated'] ?? 'now'));

        return $checkout;
    }
}


