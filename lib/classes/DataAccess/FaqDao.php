<?php
namespace DataAccess;

use Model\Faq;

/**
 * Handles all of the logic related to queries on capstone project resources in the database.
 */
class FaqDao {

    /** @var DatabaseConnection */
    private $conn;

    /** @var \Util\Logger */
    private $logger;

    /**
     * Creates a new instance of the data access object for tekbot project data.
     *
     * @param DatabaseConnection $connection the connection to use to communiate with the database
     * @param \Util\Logger $logger the logger to use to log details about the interactions with the database
     */
    public function __construct($connection, $logger) {
        $this->conn = $connection;
        $this->logger = $logger;
    }
    
    /**
     * Fetches the equipment FAQ with the provided ID
     *
     * @param string $id
     * @return \Model\EquipmentFee|boolean the equipment on success, false otherwise
     */
    public function getFaq($id) {
        try {
            $sql = '
            SELECT * 
            FROM general_faq
            WHERE id = :id
            ';
            $params = array(':id' => $id);
            $results = $this->conn->query($sql, $params);
            if (\count($results) == 0) {
                return false;
            }

            $FAQ = self::ExtractFAQFromRow($results[0]);

            return $FAQ;
        } catch (\Exception $e) {
            $this->logger->error("Failed to fetch equipment FAQ with id '$id': " . $e->getMessage());
            return false;
        }
    }

        /**
     * Fetches fees associated with a user.
     *
     * @param string $userID the ID of the user whose projects to fetch
     * @return \Model\FeesOwed[]|boolean an array of projects on success, false otherwise
     */
    public function getAllFaqs() {
        try {
            $sql = 'SELECT * 
                FROM general_faq
                ORDER BY category ASC, id ASC
            ';
            $results = $this->conn->query($sql);

            $FAQS = array();
            foreach ($results as $row) {
                $FAQ = self::ExtractFAQFromRow($row);
                $FAQS[] = $FAQ;
            }
           
            return $FAQS;
        } catch (\Exception $e) {
            $this->logger->error("Failed to get FAQS: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Adds a FAQ entry into the database.
     *
     * @param \Model\FAQ $FAQ the equipment to add
     * @return boolean true if successful, false otherwise
     */
    public function addNewFaq($faq) {
        try {
            $sql = '
            INSERT INTO general_faq VALUES (
                DEFAULT,
                :category,
                :question,
                :answer
            )
            ';
            $params = array(
                ':category' => $faq->getCategory(),
                ':question' => $faq->getQuestion(),
                ':answer' => $faq->getAnswer()
            );
            $this->conn->execute($sql, $params);
            return true;
        } catch (\Exception $e) {
            $this->logger->error('Failed to add new FAQ: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Updates a FAQ entry into the database.
     *
     * @param \Model\FAQ $faq the equipment to add
     * @return boolean true if successful, false otherwise
     */
    public function updateFAQ($faq) {
        try {
            $sql = '
            UPDATE general_faq SET 
                category = :category,
                question = :question,
                answer = :answer
            WHERE id = :id
            ';
            $params = array(
                ':id' => $faq->getFaqID(),
                ':category' => $faq->getCategory(),
                ':question' => $faq->getQuestion(),
                ':answer' => $faq->getAnswer()
            );
            $this->conn->execute($sql, $params);
            return true;
        } catch (\Exception $e) {
            $id = $faq->getFeeID();
            $this->logger->error("Failed to update faq with id '$id': " . $e->getMessage());
            return false;
        }
    }

    /**
     * Creates a new FAQ object using information from the database row
     *
     * @param mixed[] $row the row in the database from which information is to be extracted
     * @return \Model\FAQ
     */
    public static function ExtractFAQFromRow($row) {
        $faq = new FAQ($row['id']);
        $faq->setCategory($row['category']);
        $faq->setQuestion($row['question']);
        $faq->setAnswer($row['answer']);
       
        return $faq;
    }

}

