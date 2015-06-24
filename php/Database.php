<?php
/**
 * \brief Class defining a singleton object used to manage the connection
 * with the database server.
 *
 * @author Martino Pilia <martino.pilia@gmail.com>
 * @date 2015-04-29
 */
class Database {
    public static $host = "localhost";
    public static $user = "piliaMartino";
    public static $password = "elefante9886";
    public static $db = "amm15_piliaMartino";

    /**
     * \brief Reference to the single instance of this class.
     * @var Database
     */
    private static $singleton = Null;

    /**
     * \brief Protected constructor.
     */
    protected function __construct() {
        Null;
    }

    /**
     * \brief Provides the instance of the Database object.
     * @return Database The database object instance.
     */
    public static function getInstance() {
        if (!self::$singleton)
            self::$singleton = new Database();
        return self::$singleton;
    }

    /**
     * \brief Write an error page and log the database error.
     * @param  Mysqli $mysqli Databse object which generated the error.
     * @return Never: this function terminates the execution.
     */
    public static function dbError($mysqli) {
        if (isset($mysqli) && is_a($mysqli, 'mysqli')) {
            $errorId = $mysqli->connect_errno;
            $errorMsg = $mysqli->connect_error;
            error_log("Database connection error " .
                "(errno: $errorId; message: $errorMsg)", 0);
            $mysqli->close();
        }
        $errorMessage = "There was a problem in the connection with the "
            . "database. Try to return to the "
            . "<a href=\"javascript:history.back()\">previous page</a>.";
        $errorImage = "images/database_error.svg";

        FrontController::write500($errorMessage, $errorImage);
        exit();
    }

    /**
     * \brief Write an error page when the database seems to contain corrupted
     * data.
     * @return Never: this function terminates the execution.
     */
    public static function dbCorruption() {
        $errorMessage = "There is something incoherent in the database "
            . "content. Try to return to the "
            . "<a href=\"javascript:history.back()\">previous page</a>.";
        $errorImage = "images/database_corruption.svg";

        FrontController::write500($errorMessage, $errorImage);
        exit();
    }

    /**
     * \brief Check the mysqli object for a correction error.
     * @param  Mysqli $mysqli The object which instantiated the connection.
     * @return Nothing.
     */
    public static function checkConnection($mysqli) {
        if ($mysqli == Null || $mysqli->connect_errno) {
            Database::dbError($mysqli);
            exit();
        }
    }

    /**
     * \brief Check a stmt object for errors.
     * @param  stmt $stmt   The statement object.
     * @param  Mysqli $mysqli The mysqli object on which the stmt is performed.
     * @return Nothing.
     */
    public static function checkStmt($stmt, $mysqli) {
        if (isset($stmt) && is_a($stmt, 'mysqli_stmt') && $stmt->errno) {
            $errorId = $stmt->errno;
            error_log("Prepared statement execution error " .
                "(errno: $errorId)", 0);
            $mysqli->close();
            Database::dbError(Null);
            exit();
        }
    }

    /**
     * \brief Create a mysqli object and open the connection with the database.
     * @return Mysqli The mysqli object holding the connection, or Null if the
     * connection failed.
     */
    public function connect() {
        $mysqli = new mysqli();
        $mysqli->connect(
            self::$host,
            self::$user,
            self::$password,
            self::$db);
        if ($mysqli->errno)
            return Null;
        return $mysqli;
    }
}
?>
