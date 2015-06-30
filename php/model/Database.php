<?php
/**
 * \brief Abstract class defining a generic database exception.
 *
 * @author Martino Pilia <martino.pilia@gmail.com>
 * @date 2015-06-29
 */
abstract class DbException extends Exception {
    private $errno; /**< @var int    Errno code.    */
    private $error; /**< @var string Error message. */

    /**
     * \brief Create a new exception object.
     * @param int    $errno Errno code value.
     * @param string $error Error message.
     * @param string $msg   Programmer's message.
     */
    public function __construct($errno, $error, $msg) {
        parent::__construct($msg);
        $this->errno = $errno;
        $this->error = $error;
    }

    /**
     * \brief Getter for the errno code.
     * @return int The errno code.
     */
    public function getErrno() {
        return $this->errno;
    }

    /**
     * \brief Getter for the error message.
     * @return string The error message.
     */
    public function getError() {
        return $this->error;
    }
}

/**
 * \brief Class for a connection error exception.
 *
 * @author Martino Pilia <martino.pilia@gmail.com>
 * @date 2015-06-29
 */
class DbConnectionException extends DbException {

    /**
     * @copydoc DbException->__construct($errno, $error, $msg)
     */
    public function __construct($errno, $error, $msg) {
        parent::__construct($errno, $error, $msg);
    }
}

/**
 * \brief Class for a query or prepared statement failure exception.
 *
 * @author Martino Pilia <martino.pilia@gmail.com>
 * @date 2015-06-29
 */
class DbQueryException extends DbException {

    /**
     * @copydoc DbException->__construct($errno, $error, $msg)
     */
    public function __construct($errno, $error, $msg) {
        parent::__construct($errno, $error, $msg);
    }
}

/**
 * \brief Class for a database content flimsiness exception.
 *
 * @author Martino Pilia <martino.pilia@gmail.com>
 * @date 2015-06-29
 */
class DbCorruptionException extends DbException {

    /**
     * \brief Create a new exception object.
     * @param string $msg Programmer's message.
     */
    public function __construct($msg) {
        parent::__construct(Null, Null, $msg);
    }
}

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
     * \brief Create a mysqli object and open the connection with the database.
     * @return Mysqli The mysqli object holding the connection, or Null if the
     * connection failed.
     * @throws DbConnectionException on connection failure.
     */
    public function connect() {
        $mysqli = new mysqli(
            self::$host,
            self::$user,
            self::$password,
            self::$db);

        if ($mysqli->connect_errno)
            throw new DbConnectionException(
                $mysqli->connect_errno,
                $mysqli->connect_error,
                "");

        return $mysqli;
    }
}
?>
