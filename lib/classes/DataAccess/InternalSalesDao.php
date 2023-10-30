<?php

namespace DataAccess;

use Model\InternalSale;

/**
 * Handles all of the logic related to queries on loading/editing messages in the database.
 */
class InternalSalesDao {

    /** @var DatabaseConnection */
    private $conn;

    /** @var \Util\Logger */
    private $logger;

    /**
     * Creates a new instance of the data access object for internal sales.
     *
     * @param DatabaseConnection $connection the connection used to communicate with the database
     * @param \Util\Logger $logger the logger to use to log details about the interactions with the database
     */
    public function __construct($connection, $logger) {
        $this->conn = $connection;
        $this->logger = $logger;
    }

/**
 * Fetches sales
 *
 * @return \Model\InternalSale|boolean the sale on success, false otherwise
 */   
    public function getSales() {
        try {
            $sql = '
            SELECT * FROM `tekbots_internalsales` ORDER BY `tekbots_internalsales`.`id` DESC';
            $results = $this->conn->query($sql);
            //echo $sql;
            $sales = array();
            foreach ($results as $row) {
                $sale = self::ExtractSaleFromRow($row);
                $sales[] = $sale;
            }
            return $sales;
        } catch (\Exception $e) {
            $this->logger->error('Failed to get any sales: ' . $e->getMessage());
            return false;
        }
    }

/**
 * Fetches the sale with the provided ID
 *
 * @param string $id
 * @return \Model\InternalSale|boolean the equipment on success, false otherwise
 */
    public function getSale($id) {
        try {
            $sql = '
            SELECT * 
            FROM `tekbots_internalsales`
            WHERE tekbots_internalsales.id = :id
            ';
            $params = array(':id' => $id);
            $results = $this->conn->query($sql, $params);
            if (\count($results) == 0) {
                return false;
            }

            $sale = self::ExtractSaleFromRow($results[0]);

            return $sale;
        } catch (\Exception $e) {
            $this->logger->error("Failed to fetch sale with id '$id': " . $e->getMessage());
            return false;
        }
    }

/**
 * Add a single sale
 * @return \Model\InternalSale added on success, false otherwise
 */
   public function addSale($sale) {
        try {
            $sql = '
            INSERT INTO tekbots_internalsales 
            (email, account, amount, buyer, seller, description)
            VALUES
            (:email, :account, :amount, :buyer, :seller, :description)
            ';
            $params = array(
				//':saleId' => $sale->getSaleId(),
                //':timestamp' => $sale->getTimestamp(),
                ':email' => $sale->getEmail(),
				':account' => $sale->getAccount(),
                ':amount' => $sale->getAmount(),
                ':buyer' => $sale->getBuyer(),
                ':seller' => $sale->getSeller(),
                ':description' => $sale->getDescription()
                //':processed' => $sale->getProcessed()
            );
            $this->conn->execute($sql,$params);
			
            return true;
        } catch (\Exception $e) {
            $this->logger->error('Failed to add sale: ' . $e->getMessage());
            return false;
        }
    }

/**
 * Delete a single sale
 * @return \Model\InternalSale added on success, false otherwise
 */
    public function deleteSaleByID($saleId) {
        try {
            $sql = '
            DELETE FROM tekbots_internalsales
            WHERE id = :id
            ';
            $params = array(
                ':id' => $saleId,
            );
            $this->conn->execute($sql, $params);

            return true;
        } catch (\Exception $e) {
            $this->logger->error('Failed to remove sale: ' . $e->getMessage());
            return false;
        }
    }

/**
 * Fetches sales that are unprocesssed
 *
 * @return \Model\InternalSale|boolean the sale on success, false otherwise
 */   
public function getUnprocessed() {
    try {
        $sql = '
        SELECT * FROM tekbots_internalsales
        WHERE processed = "0000-00-00 00:00:00"
        ';
        $results = $this->conn->query($sql);
        //echo $sql;
        //unprocessed sales
        $sales = array();
        foreach ($results as $row) {
            $sale = self::ExtractSaleFromRow($row);
            $sales[] = $sale;
        }
        return $sales;
    } catch (\Exception $e) {
        $this->logger->error('Failed to get unprocessed sales: ' . $e->getMessage());
        return false;
    }
}

/**
 * Updates unprocessed sales into processed sales
 *
 * @return \Model\InternalSale|boolean the sale on success, false otherwise
 */   
public function processAll() {
    try {
        $sql = '
        UPDATE `tekbots_internalsales` 
        SET `processed`= NOW() 
        WHERE `processed` = "0000-00-00 00:00:00"
        ';
        $results = $this->conn->execute($sql);
        
        return true;
    } catch (\Exception $e) {
        $this->logger->error('Failed to update unprocessed sales: ' . $e->getMessage());
        return false;
    }
}

/**
 * Creates a new sale object using information from the database row
 *
 * @param mixed[] $row the row in the database from which information is to be extracted
 * @return \Model\InternalSale
 */
    public static function ExtractSaleFromRow($row) {
        $sale = new InternalSale($row['id']);

		if(isset($row['TIMESTAMP'])){
			$sale->setTimestamp($row['TIMESTAMP']);
		}
		if(isset($row['email'])){
			$sale->setEmail($row['email']);
		}
		if(isset($row['account'])){
			$sale->setAccount($row['account']);
		}
        if(isset($row['amount'])){
			$sale->setAmount($row['amount']);
		}
		if(isset($row['buyer'])){
			$sale->setBuyer($row['buyer']);
		}
		if(isset($row['seller'])){
			$sale->setSeller($row['seller']);
		}
		if(isset($row['description'])){
			$sale->setDescription($row['description']);
		}
		if(isset($row['processed'])){
			$sale->setProcessed($row['processed']);
       
        return $sale;
    }

    }

}
?>