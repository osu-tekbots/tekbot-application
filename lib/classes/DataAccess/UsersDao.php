<?php
// Updated 11/5/2019
namespace DataAccess;

use Model\User;
use Model\UserAccessLevel;


/**
 * Contains logic for database interactions with user data in the database. 
 * 
 * DAO stands for 'Data Access Object'
 */
class UsersDao {

    /** @var DatabaseConnection */
    private $conn;

    /** @var \Util\Logger */
    private $logger;

    /** @var boolean */
    private $echoOnError;

    /**
     * Constructs a new instance of a User Data Access Object.
     *
     * @param DatabaseConnection $connection the connection used to perform user-related queries on the database
     * @param \Util\Logger $logger the logger to use for logging messages and errors associated with fetching user data
     * @param boolean $echoOnError determines whether to echo an error whether or not a logger is present
     */
    public function __construct($connection, $logger = null, $echoOnError = false) {
        $this->logger = $logger;
        $this->conn = $connection;
        $this->echoOnError = $echoOnError;
    }

    /**
     * Fetches all the users from the database.
     * 
     * If an error occurs during the fetch, the function will return `false`.
     *
     * @return User[]|boolean an array of User objects if the fetch succeeds, false otherwise
     */
    public function getAllUsers() {
        try {
            $sql = 'SELECT * FROM user, user_access_level ';
            $sql .= 'WHERE access_level_id = user_access_level.user_access_level_id ';
            $sql .= 'ORDER BY last_name ASC';
            $result = $this->conn->query($sql);

            return \array_map('self::ExtractUserFromRow', $result);
        } catch (\Exception $e) {
            $this->logError('Failed to fetch users: ' . $e->getMessage());

            return false;
        }
    }
	
	/**
     * Fetches all the users from the database with requested type.
     * 
     * If an error occurs during the fetch, the function will return `false`.
     *
     * @return User[]|boolean an array of User objects if the fetch succeeds, false otherwise
     */
    public function getAllUsersByType($levelName) {
        try {
            $sql = 'SELECT * FROM user, user_access_level 
					WHERE access_level_id = user_access_level.user_access_level_id 
					AND user_access_level.user_access_name = :levelName 
					ORDER BY last_name ASC
					';
            
			$params = array(':levelName' => $levelName);
			$result = $this->conn->query($sql,$params);

            return \array_map('self::ExtractUserFromRow', $result);
        } catch (\Exception $e) {
            $this->logError('Failed to fetch users: ' . $e->getMessage());

            return false;
        }
    }

    /**
     * Fetches a single user with the given ID from the database.
     *
     * @param string $id the ID of the user to fetch
     * @return User|boolean the corresponding User from the database if the fetch succeeds and the user exists, 
     * false otherwise
     */
    public function getUserByID($id) {
        try {
            $sql = '
            SELECT * 
            FROM user, user_access_level 
            WHERE user.user_id = :id 
            AND access_level_id = user_access_level.user_access_level_id
            ';
            $params = array(':id' => $id);
            $result = $this->conn->query($sql, $params);
            if (\count($result) == 0) {
                return false;
            }

            return self::ExtractUserFromRow($result[0]);
        } catch (\Exception $e) {
            $this->logError('Failed to fetch single user by ID: ' . $e->getMessage());

            return false;
        }
    }

    /**
     * Fetches a single user with the given ID from the database.
     *
     * @param string $id the ID of the user to fetch
     * @return User|boolean the corresponding User from the database if the fetch succeeds and the user exists, 
     * false otherwise
     */
    public function getUserByONID($id) {
        try {
            $sql = '
            SELECT * 
            FROM user, user_access_level 
            WHERE onid = :onid 
            AND access_level_id = user_access_level.user_access_level_id
            ';
            $params = array(':onid' => $id);
            $result = $this->conn->query($sql, $params);
            if (\count($result) == 0) {
                return false;
            }

            return self::ExtractUserFromRow($result[0]);
        } catch (\Exception $e) {
            $this->logError('Failed to fetch single user by ID: ' . $e->getMessage());

            return false;
        }
    }

    
    /**
     * Adds a new user to the database.
     *
     * @param \Model\User $user the user to add to the database
     * @return boolean true if the query execution succeeds, false otherwise.
     */
    public function addNewUser($user) {
        try {
            $sql = 'INSERT INTO user 
                (
                    user_id, 
                    first_name, 
                    last_name,
                    email,
                    phone,
                    onid,
                    access_level_id,
                    date_created,
                    last_login_date
                ) 
                VALUES (
                    :id,
                    :fname,
                    :lname,
                    :email,
                    :phone,
                    :onid,
                    :type,
                    :datec,
                    :datel
                )';
            $params = array(
                ':id' => $user->getUserID(),
                ':type' => $user->getAccessLevelID()->getId(),
                ':fname' => $user->getFirstName(),
                ':lname' => $user->getLastName(),
                ':email' => $user->getEmail(),
                ':phone' => $user->getPhone(),
                ':onid' => $user->getOnid(),
                ':datec' => QueryUtils::FormatDate($user->getDateCreated()),
                ':datel' => QueryUtils::FormatDate($user->getDateLastLogin()),
            );
            $this->conn->execute($sql, $params);

            return true;
        } catch (\Exception $e) {
            $this->logError('Failed to add new user: ' . $e->getMessage());

            return false;
        }
    }

    /**
     * Updates an existing user in the database. 
     * 
     * This function only updates trivial user information, such as the type, first and last names, salutation, majors, 
     * affiliations, and contact information.
     *
     * @param \Model\User $user the user to update
     * @return boolean true if the query execution succeeds, false otherwise.
     */
    public function updateUser($user) {
        try {
            $sql = 'UPDATE user
                SET 
                    access_level_id = :type,
                    first_name = :fname, 
                    last_name = :lname, 
                    email = :email, 
                    onid = :onid, 
                    phone = :phone, 
                    date_updated = :dateu,
                    last_login_date = :datel
                WHERE user_id = :id';
            $params = array(
                ':type' => $user->getAccessLevelID()->getId(),
                ':fname' => $user->getFirstName(),
                ':lname' => $user->getLastName(),
                ':email' => $user->getEmail(),
				':onid' => $user->getOnid(),
                ':phone' => $user->getPhone(),
                ':dateu' => QueryUtils::FormatDate($user->getDateUpdated()),
                ':datel' => QueryUtils::FormatDate($user->getDateLastLogin()),
                ':id' => $user->getUserID()
            );
            $this->conn->execute($sql, $params);

            return true;
        } catch (\Exception $e) {
            $this->logError('Failed to update user: ' . $e->getMessage());

            return false;
        }
    }

    /**
     * Creates a new User object by extracting the information from a row in the database.
     *
     * @param string[] $row a row from the database containing user information
     * @return \Model\User
     */
    public static function ExtractUserFromRow($row) {
        $user = new User($row['user_id']);
        $user->setAccessLevelID(self::ExtractUserAccessLevelFromRow($row, true));
        $user->setFirstName($row['first_name']);
        $user->setLastName($row['last_name']);
        $user->setEmail($row['email']);
        $user->setPhone($row['phone']);
        $user->setOnid($row['onid']);
        $user->setDateCreated(new \DateTime(($row['date_created'] == '' ? 'now' : $row['date_created'])));
        $user->setDateUpdated(new \DateTime(($row['date_updated'] == '' ? 'now' : $row['date_updated'])));
        $user->setDateLastLogin(new \DateTime(($row['last_login_date'] == '' ? 'now' : $row['last_login_date'])));

        return $user;
    }

        /**
     * Creates a new UserType object by extracting the necessary information from a row in a database.
     * 
     * The extraction will default to using the UserType ID from the user table if it is present so that this
     * function can be used on the user table alone without joining on the user type table.
     *
     * @param mixed[] $row the row from the database
     * @param boolean $userInRow flag indicating whether entries from the user table are in the row or not
     * @return \Model\UserType the user type extracted from the row
     */
    public static function ExtractUserAccessLevelFromRow($row, $userInRow = false) {
        $idKey = 'user_access_level_id';
        $name = $row['user_access_name'];
        return new UserAccessLevel(\intval($row[$idKey]), $name);
    }

        /**
     * Logs an error if a logger was provided to the class when it was constructed.
     * 
     * Essentially a wrapper around the error logging so we don't cause the equivalent of a null pointer exception.
     *
     * @param string $message the message to log.
     * @return void
     */
    private function logError($message) {
        if ($this->logger != null) {
            $this->logger->error($message);
        }
        if ($this->echoOnError) {
            echo "$message\n";
        }
    }
}
?>
