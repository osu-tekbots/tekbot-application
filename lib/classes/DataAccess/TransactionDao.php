<?php
namespace DataAccess;

use Model\Transaction;
use Model\TransactionDelivery;
use Model\TransactionItem;


/**
 * Contains logic for database interactions with transaction data in the database. 
 * 
 * DAO stands for 'Data Access Object'
 */
class TransactionDao {

    /** @var DatabaseConnection */
    private $conn;

    /** @var \Util\Logger */
    private $logger;

    /**
     * Constructs a new instance of a transaction Data Access Object.
     *
     * @param DatabaseConnection $connection the connection used to perform transaction-related queries on the database
     * @param \Util\Logger $logger the logger to use for logging messages and errors associated with fetching transaction data
     */
    public function __construct($connection, $logger = null) {
        $this->logger = $logger;
        $this->conn = $connection;
    }


    /**
     * Retrieves all transactions. Allows the employee page's ID input to show possible
     * matches, decreasing the number of characters that must be typed to find a
     * transaction.
     * 
     * @return array{\Model\Transaction}|false
     */
    public function getAllTransactions() {
        try {
            $sql = 'SELECT * FROM transaction INNER JOIN transaction_delivery ON td_id = t_td_id;';
            $results = $this->conn->query($sql);
    
            return \array_map('self::ExtractTransactionFromRow', $results);
        } catch (\Exception $e) {
            $this->logger->error("Failed to get transactions: " . $e->getMessage());

            return false;
        }
    }


    /**
     * Fetches the given transaction (not including items) by its database ID (called
     * EXT_TRANS_ID in Touchnet).
     * 
     * @param string $id
     * 
     * @return \Model\Transaction|false
     */
    public function getTransaction($id) {
        try {
            $sql = 'SELECT * FROM transaction
                    INNER JOIN transaction_delivery ON td_id = t_td_id
                WHERE t_id = :id;
            ';
            $params = ['id' => $id];
            
            $results = $this->conn->query($sql, $params);

            if (\count($results) == 0) {
                return false;
            }
    
            return self::ExtractTransactionFromRow($results[0]);
        } catch (\Exception $e) {
            $this->logger->error("Failed to get transaction $id: " . $e->getMessage());

            return false;
        }
    }


    /**
     * Gets all items for the given transaction by the transaction's database ID (called
     * EXT_TRANS_ID in Touchnet).
     * 
     * @param string $id
     * 
     * @return \Model\Transaction|false
     */
    public function getTransactionItems($id) {
        try {
            $sql = 'SELECT * FROM transaction_item WHERE ti_t_id = :t_id;';
            $params = ['t_id' => $id];
            $results = $this->conn->query($sql, $params);
    
            return \array_map('self::ExtractTransactionItemFromRow', $results);
        } catch (\Exception $e) {
            $this->logger->error("Failed to get transaction items: " . $e->getMessage());

            return false;
        }
    }


    /**
     * Retrieves all delivery methods (including ones that stretch the meaning of that
     * term, like "Pickup") from the database.
     * 
     * @return array{\Model\TransactionDelivery}|false
     */
    public function getAllDeliveryMethods() {
        try {
            $sql = 'SELECT * FROM transaction_delivery;';
            $results = $this->conn->query($sql);

            return \array_map('self::ExtractTransactionDeliveryFromRow', $results);
        } catch (\Exception $e) {
            $this->logger->error("Failed to get delivery methods: " . $e->getMessage());

            return false;
        }
    }


    /**
     * Retrieves the given delivery method from the database by its ID.
     * 
     * @param string $id
     * @return \Model\TransactionDelivery|false
     */
    public function getDeliveryMethod($id) {
        try {
            $sql = 'SELECT * FROM transaction_delivery WHERE td_id = :id;';
            $params = ['id' => $id];

            $results = $this->conn->query($sql, $params);

            if (\count($results) == 0) {
                return false;
            }

            return self::ExtractTransactionDeliveryFromRow($results[0]);
        } catch (\Exception $e) {
            $this->logger->error("Failed to get delivery method $id: " . $e->getMessage());

            return false;
        }
    }

    
    /**
     * Retrieves all true delivery methods (omitting ones that aren't actually delivery,
     * like "Pickup") from the database. Used to present a dropdown select for users who
     * pick shipping at checkout.
     * 
     * NOTE: any methods that should be omitted must be added to this function's
     * implementation.
     * 
     * @return \Model\TransactionDelivery|false
     */
    public function getShippingOptions() {
        try {
            $sql = 'SELECT * FROM transaction_delivery WHERE td_id != :pickup_id;';
            $params = ['pickup_id' => TransactionDelivery::PICKUP];
            $results = $this->conn->query($sql, $params);
    
            return \array_map('self::ExtractTransactionDeliveryFromRow', $results);
        } catch (\Exception $e) {
            $this->logger->error("Failed to get shipping options: " . $e->getMessage());

            return false;
        }
    }


    /**
     * Adds a new transaction's data to the database.
     *
     * @param Model\Transaction $transaction
     * @param array{Model\TransactionItem} $transactionItems
     *
     * @return boolean Whether adding the transaction succeeded
     */
    public function addTransaction($transaction, $transactionItems) {
        try {
            $sql = 'INSERT INTO `transaction`(
                t_id,
                t_amount,
                t_status,
                t_td_id,
                t_u_id,
                t_shipping_name,
                t_shipping_email,
                t_shipping_phone,
                t_shipping_address1,
                t_shipping_address2,
                t_shipping_city,
                t_shipping_state,
                t_shipping_country,
                t_shipping_code,
                t_tpg_trans_id,
                t_sys_tracking_id,
                t_card_type,
                t_card_name,
                t_receipt_email,
                t_employee_notes,
                t_date_paid,
                t_date_fulfilled,
                t_date_created,
                t_date_updated
            ) VALUES (
                :id,
                :amount,
                :status,
                :td_id,
                :u_id,
                :shipping_name,
                :shipping_email,
                :shipping_phone,
                :shipping_address1,
                :shipping_address2,
                :shipping_city,
                :shipping_state,
                :shipping_country,
                :shipping_code,
                :tpg_trans_id,
                :sys_tracking_id,
                :card_type,
                :card_name,
                :receipt_email,
                :employee_notes,
                :date_paid,
                :date_fulfilled,
                :date_created,
                :date_updated
            )';
            $params = [
                'id' => $transaction->getTransactionId(),
                'amount' => $transaction->getAmount(),
                'status' => $transaction->getStatus(),
                'td_id' => $transaction->getDeliveryMethod()->getID(),
                'u_id' => $transaction->getUserId(),
                'shipping_name' => $transaction->getShippingName(),
                'shipping_email' => $transaction->getShippingEmail(),
                'shipping_phone' => $transaction->getShippingPhone(),
                'shipping_address1' => $transaction->getShippingAddress1(),
                'shipping_address2' => $transaction->getShippingAddress2(),
                'shipping_city' => $transaction->getShippingCity(),
                'shipping_state' => $transaction->getShippingState(),
                'shipping_country' => $transaction->getShippingCountry(),
                'shipping_code' => $transaction->getShippingCode(),
                'tpg_trans_id' => $transaction->getTpgTransId(),
                'sys_tracking_id' => $transaction->getSysTrackingId(),
                'card_type' => $transaction->getCardType(),
                'card_name' => $transaction->getCardName(),
                'receipt_email' => $transaction->getReceiptEmail(),
                'employee_notes' => $transaction->getEmployeeNotes(),
                'date_paid' => QueryUtils::FormatDate($transaction->getDatePaid()),
                'date_fulfilled' => QueryUtils::FormatDate($transaction->getDateFulfilled()),
                'date_created' => QueryUtils::FormatDate($transaction->getDateCreated()),
                'date_updated' => QueryUtils::FormatDate($transaction->getDateUpdated())
            ];
            $result = $this->conn->execute($sql, $params);

            foreach ($transactionItems as $item) {
                $sql = 'INSERT INTO `transaction_item` (
                    `ti_id`, `ti_t_id`, `ti_type`, `ti_name`, `ti_stocknumber`, `ti_price`,
                    `ti_quantity`, `ti_date_created`, `ti_date_updated`
                ) VALUES (
                    :id, :t_id, :type, :name, :stocknumber, :price, :quantity,
                    :date_created, :date_updated
                );';
                $params = [
                    'id' => $item->getItemID(),
                    't_id' => $transaction->getTransactionID(),
                    'type' => $item->getType(),
                    'name' => $item->getName(),
                    'stocknumber' => $item->getStocknumber(),
                    'price' => $item->getPrice(),
                    'quantity' => $item->getQuantity(),
                    'date_created' => QueryUtils::FormatDate($item->getDateCreated()),
                    'date_updated' => QueryUtils::FormatDate($item->getDateUpdated()),
                ];
                $this->conn->execute($sql, $params);
            }

            return true;
        } catch (\Exception $e) {
            $this->logger->error('Failed to add transaction: ' . $e->getMessage());

            return false;
        }
    }


    /**
     * Adds a new delivery method for users to pick from.
     * 
     * @param Model\TransactionDelivery $delivery
     *
     * @return boolean Whether adding the delivery method succeeded
     */
    public function addDeliveryMethod($delivery) {
        try {
            $sql = 'INSERT INTO `transaction_delivery` (td_name, td_base_cost, td_item_cost)
                VALUES (:name, :base_cost, :item_cost);';
            $params = [
                'name' => $delivery->getName(),
                'base_cost' => $delivery->getBaseCost(),
                'item_cost' => $delivery->getItemCost(),
            ];
            $result = $this->conn->execute($sql, $params);

            return true;
        } catch (\Exception $e) {
            $this->logger->error('Failed to add delivery method: ' . $e->getMessage());

            return false;
        }
    }


    /**
     * Updates a new transaction's data in the database.
     *
     * @param Model\Transaction $transaction
     *
     * @return boolean Whether updating the transaction succeeded
     */
    public function updateTransaction($transaction) {
        try {
            $sql = 'UPDATE `transaction`
                SET t_amount = :amount,
                    t_status = :status,
                    t_td_id = :delivery_method,
                    t_u_id = :u_id,
                    t_shipping_name = :shipping_name,
                    t_shipping_email = :shipping_email,
                    t_shipping_phone = :shipping_phone,
                    t_shipping_address1 = :shipping_address1,
                    t_shipping_address2 = :shipping_address2,
                    t_shipping_city = :shipping_city,
                    t_shipping_state = :shipping_state,
                    t_shipping_country = :shipping_country,
                    t_shipping_code = :shipping_code,
                    t_tpg_trans_id = :tpg_trans_id,
                    t_sys_tracking_id = :sys_tracking_id,
                    t_card_type = :card_type,
                    t_card_name = :card_name,
                    t_receipt_email = :receipt_email,
                    t_employee_notes = :employee_notes,
                    t_date_paid = :date_paid,
                    t_date_fulfilled = :date_fulfilled,
                    t_date_created = :date_created,
                    t_date_updated = :date_updated
                WHERE t_id = :id;
            ';
            $params = [
                'amount' => $transaction->getAmount(),
                'status' => $transaction->getStatus(),
                'delivery_method' => $transaction->getDeliveryMethod()->getID(),
                'u_id' => $transaction->getUserId(),
                'shipping_name' => $transaction->getShippingName(),
                'shipping_email' => $transaction->getShippingEmail(),
                'shipping_phone' => $transaction->getShippingPhone(),
                'shipping_address1' => $transaction->getShippingAddress1(),
                'shipping_address2' => $transaction->getShippingAddress2(),
                'shipping_city' => $transaction->getShippingCity(),
                'shipping_state' => $transaction->getShippingState(),
                'shipping_country' => $transaction->getShippingCountry(),
                'shipping_code' => $transaction->getShippingCode(),
                'tpg_trans_id' => $transaction->getTpgTransId(),
                'sys_tracking_id' => $transaction->getSysTrackingId(),
                'card_type' => $transaction->getCardType(),
                'card_name' => $transaction->getCardName(),
                'receipt_email' => $transaction->getReceiptEmail(),
                'employee_notes' => $transaction->getEmployeeNotes(),
                'date_paid' => QueryUtils::FormatDate($transaction->getDatePaid()),
                'date_fulfilled' => QueryUtils::FormatDate($transaction->getDateFulfilled()),
                'date_created' => QueryUtils::FormatDate($transaction->getDateCreated()),
                'date_updated' => QueryUtils::FormatDate($transaction->getDateUpdated()),
                'id' => $transaction->getTransactionId(),
            ];
            $result = $this->conn->execute($sql, $params);

            return true;
        } catch (\Exception $e) {
            $this->logger->error('Failed to update transaction: ' . $e->getMessage());

            return false;
        }
    }


    /**
     * Sets the given delivery method as the default-selected one on the user payment
     * page, unsetting any previous default.
     * 
     * @param string $id
     * @return boolean Whether setting the default delivery method succeeded
     */
    public function setDefaultDeliveryMethod($id) {
        try {
            $sql = 'UPDATE `transaction_delivery` SET `td_is_default` = `td_id` = :id';
            $params = ['id' => $id];
            $result = $this->conn->execute($sql, $params);

            return true;
        } catch (\Exception $e) {
            $this->logger->error('Failed to set default delivery method: ' . $e->getMessage());

            return false;
        }
    }


    /**
     * Updates the given delivery method (identified by ID), excluding its is_default
     * flag.
     * 
     * @param \Model\TransactionDelivery $delivery
     * @return boolean Whether updating the delivery method succeeded
     */
    public function updateDeliveryMethod($delivery) {
        try {
            $sql = 'UPDATE `transaction_delivery`
                SET `td_name` = :name,
                    `td_base_cost` = :base_cost,
                    `td_item_cost` = :item_cost
                WHERE `td_id` = :id
            ';
            $params = [
                'id' => $delivery->getID(),
                'name' => $delivery->getName(),
                'base_cost' => $delivery->getBaseCost(),
                'item_cost' => $delivery->getItemCost(),
            ];
            $result = $this->conn->execute($sql, $params);

            return true;
        } catch (\Exception $e) {
            $this->logger->error('Failed to update delivery method: ' . $e->getMessage());

            return false;
        }
    }


    /**
     * Creates a new Transaction object by extracting the information from a row in the database.
     *
     * @param string[] $row a row from the database containing transaction information
     * @return \Model\Transaction
     */
    public static function ExtractTransactionFromRow($row) {
        $transaction = new Transaction($row['t_id']);
        $transaction->setAmount($row['t_amount']);
        $transaction->setStatus($row['t_status']);
        $transaction->setDeliveryMethod(self::ExtractTransactionDeliveryFromRow($row));
        $transaction->setUserId($row['t_u_id']);
        $transaction->setShippingName($row['t_shipping_name']);
        $transaction->setShippingEmail($row['t_shipping_email']);
        $transaction->setShippingPhone($row['t_shipping_phone']);
        $transaction->setShippingAddress1($row['t_shipping_address1']);
        $transaction->setShippingAddress2($row['t_shipping_address2']);
        $transaction->setShippingCity($row['t_shipping_city']);
        $transaction->setShippingState($row['t_shipping_state']);
        $transaction->setShippingCountry($row['t_shipping_country']);
        $transaction->setShippingCode($row['t_shipping_code']);
        $transaction->setTpgTransId($row['t_tpg_trans_id']);
        $transaction->setSysTrackingId($row['t_sys_tracking_id']);
        $transaction->setCardType($row['t_card_type']);
        $transaction->setCardName($row['t_card_name']);
        $transaction->setReceiptEmail($row['t_receipt_email']);
        $transaction->setEmployeeNotes($row['t_employee_notes']);
        $transaction->setDatePaid($row['t_date_paid'] ? new \DateTime($row['t_date_paid']) : null);
        $transaction->setDateFulfilled($row['t_date_fulfilled'] ? new \DateTime($row['t_date_fulfilled']) : null);
        $transaction->setDateCreated(new \DateTime($row['t_date_created']));
        $transaction->setDateUpdated(new \DateTime($row['t_date_updated']));
        
        return $transaction;
    }


    /**
     * Creates a new TransactionDelivery object by extracting the information from a row in the database.
     *
     * @param string[] $row a row from the database containing transaction information
     * @return \Model\TransactionDelivery
     */
    public static function ExtractTransactionDeliveryFromRow($row) {
        $delivery = new TransactionDelivery($row['td_id']);
        $delivery->setName($row['td_name']);
        $delivery->setIsDefault($row['td_is_default']);
        $delivery->setBaseCost($row['td_base_cost']);
        $delivery->setItemCost($row['td_item_cost']);
        
        return $delivery;
    }


    /**
     * Creates a new TransactionItem object by extracting the information from a row in the database.
     *
     * @param string[] $row a row from the database containing transaction information
     * @return \Model\TransactionItem
     */
    public static function ExtractTransactionItemFromRow($row) {
        $item = new TransactionItem($row['ti_id']);
        $item->setTransactionID($row['ti_t_id']);
        $item->setType($row['ti_type']);
        $item->setName($row['ti_name']);
        $item->setStocknumber($row['ti_stocknumber']);
        $item->setPrice($row['ti_price']);
        $item->setQuantity($row['ti_quantity']);
        $item->setDateCreated(new \DateTime($row['ti_date_created']));
        $item->setDateUpdated(new \DateTime($row['ti_date_updated']));
        
        return $item;
    }
}
