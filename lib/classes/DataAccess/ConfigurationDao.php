<?php

namespace DataAccess;

use Model\Configuration;

/**
 * Handles all of the logic related to queries on loading/editing messages in the database.
 */
class ConfigurationDao {

    /** @var DatabaseConnection */
    private $conn;

    /** @var \Util\Logger */
    private $logger;

    /**
     * Creates a new instance of the data access object for the configuration.
     *
     * @param DatabaseConnection $connection the connection used to communicate with the database
     * @param \Util\Logger $logger the logger to use to log details about the interactions with the database
     */
    public function __construct($connection, $logger) {
        $this->conn = $connection;
        $this->logger = $logger;
    }

    /**
     * Fetches all columns.
     * 
     * @return Model\Configuration The most recent configuration row
     */
    public function getConfiguration() {
        try {
            $sql = '
            SELECT MAX(id), `last_cron_email`
			FROM `configuration`
			';
            $results = $this->conn->query($sql);

            $configuration = self::ExtractConfigurationFromRow($results[0]);
            return $configuration;
        } catch (\Exception $e) {
            $this->logger->error('Failed to get any configuration: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Updates the database for the given configuration object.
     * 
     * @param Model\Configuration $configuration  The new configuration data
     * 
     * @return bool Whether the update succeeded
     */
    public function updateConfiguration($configuration) {
        try {
            $sql = '
            UPDATE configuration SET
                last_cron_email = :last_cron_email
            WHERE id = :id
            ';
            $params = array(
				':id' => $configuration->getId(),
                ':last_cron_email' => $configuration->getLastCronEmailTime()
            );
            $this->conn->execute($sql, $params);
			
            return true;
        } catch (\Exception $e) {
            $this->logger->error('Failed to update configuration: ' . $e->getMessage());
            return false;
        }
    }
	
	/**
     * Adds a new row to the database, overwriting the configuration that will be returned
     * 
     * @param Model\Configuration $configuration  The new configuration data
     * 
     * @return bool Whether the overwrite succeeded
     */
    public function overwriteConfiguration($configuration) {
        try {
            $sql = '
            INSERT INTO configuration 
			(last_cron_email) VALUES
			(:last_cron_email)
			';
            $params = array(':last_cron_email' => $configuration->getLastCronEmailTime());
            $results = $this->conn->execute($sql, $params);

			return true;
        } catch (\Exception $e) {
            $this->logger->error('Configuration not overwritten: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Creates a new Configuration object using information from the database row
     *
     * @param mixed[] $row the row in the database from which information is to be extracted
     * 
     * @return \Model\Configuration
     */
    public static function ExtractConfigurationFromRow($row) {
        $configuration = new Configuration($row['id'] ?? $row['MAX(id)']);

		if(isset($row['last_cron_email'])){
			$configuration->setLastCronEmailTime($row['last_cron_email']);
		}
       
        return $configuration;
    }

   
}

?>