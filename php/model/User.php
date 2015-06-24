<?php

include __DIR__ . "/../Database.php";
include __DIR__ . "/Admin.php";

/**
 * \brief Abstract class used as template for enumeration classes.
 *
 * @author Martino Pilia <martino.pilia@gmail.com>
 * @date 2015-04-29
 */
abstract class Enum {

    /**
     * \brief Check wether a variable is a valid enumeration value.
     * @param  $value Variable to be checked.
     * @return boolean True if the parameter is a valid value for the current
     * enumeration, false otherwise.
     */
    public static function isValid($value) {
        $reflector = new ReflectionClass(get_called_class());
        return in_array(
            $value,
            array_values($reflector->getConstants()),
            $strict = true);
    }
}

/**
 * \brief Enumeration class for user roles.
 *
 * @author Martino Pilia <martino.pilia@gmail.com>
 * @date 2015-04-29
 */
abstract class Role extends Enum {
    const ADMIN = 'Admin';
    const USER = 'User';
}

/**
 * \brief Exception raised when the database entry for an user seems invalid.
 *
 * @author Martino Pilia <martino.pilia@gmail.com>
 * @date 2015-06-11
 */
class InvalidUserException extends Exception {
    public function __construct() {
        Null;
    }
}

/**
 * \brief Class defining a generic user.
 *
 * @author Martino Pilia <martino.pilia@gmail.com>
 * @date 2015-04-29
 */
class User {
    private $id;       /**< User Id in the database. */
    private $username; /**< Username. */
    private $first;    /**< First name. */
    private $last;     /**< Last name. */
    private $email;    /**< E-mail. */
    private $avatar;   /**< Avatar image file. */
    protected $role;   /**< User's role. */

    /**
     * \brief Create an object representing a User.
     * @param string $username User username.
     * @param string $first    User first name.
     * @param string $last     User last name.
     * @param string $email    User email address.
     * @param string $avatar   User avatar filename.
     */
    public function __construct($id, $username, $first, $last,
            $email, $avatar) {
        $this->id = $id;
        $this->username = $username;
        $this->first = $first;
        $this->last = $last;
        $this->email = $email;
        $this->avatar = $avatar;
        $this->role = Role::USER;
    }

    /* setters */
    /**
     * \brief Set first name.
     * @param first A string containing the user's first name.
     */
    public function setFirst($first) {
        $this->first = $first;
    }

    /**
     * \brief Set last name.
     * @param last A string containing the user's last name.
     */
    public function setLast($last) {
        $this->last = $last;
    }

    /**
     * \brief Set email.
     * @param email A string containing the user's email.
     */
    public function setEmail($email) {
        $this->email = $email;
    }

    /**
     * \brief Set avatar.
     * @param avatar Avatar filename.
     */
    public function setAvatar($avatar) {
        $this->avatar = $avatar;
    }

    /* getters */
    /**
     * \brief Return lusername.
     * @return A string containing the user's username.
     */
    public function getUsername() {
        return $this->username;
    }

    /**
     * \brief Return first name.
     * @return A string containing the user's first name.
     */
    public function getFirst() {
        return $this->first;
    }

    /**
     * \brief Return last name.
     * @return A string containing the user's last name.
     */
    public function getLast() {
        return $this->last;
    }

    /**
     * \brief Return email.
     * @return A string containing the user's email.
     */
    public function getEmail() {
        return $this->email;
    }

    /**
     * \brief Return role.
     * @return A string containing the user's role.
     */
    public function getRole() {
        return $this->role;
    }

    /**
     * \brief Return avatar.
     * @return A string containing the user's avatar filename.
     */
    public function getAvatar() {
        return $this->avatar;
    }

    /**
     * \brief Return user id.
     * @return An integer containing the user's id.
     */
    public function getId() {
        return $this->id;
    }

    /**
     * \brief Create a user object extracting its data from the database.
     * @param $id User id.
     * @return An object defining the user.
     * @throws InvalidUserException When the database entry for a user seems
     * invalid.
     */
    public static function newUserById($id) {
        $mysqli = Database::getInstance()->connect(); /* open db connection */
        Database::checkConnection($mysqli); /* check db connection */

        $query = "SELECT Username, Email, Avatar, First, Last, Role " .
                 "FROM Users WHERE Id=$id";

        $result = $mysqli->query($query);
        if ($mysqli->errno > 0) {
            error_log("Query error $mysqli->errno: $mysqli->error", 0);
            Database::dbError($mysqli);
            exit();
        }
        while ($row = $result->fetch_object()) {
            $username = $row->Username;
            $email = $row->Email;
            $avatar = $row->Avatar;
            $first = $row->First;
            $last = $row->Last;
            $role = $row->Role;
        }
        $mysqli->close(); /* close db connection */

        if (!Role::isValid($role))
            throw new InvalidUserException();

        /* create an object of the right class for the user, and fill it with
         * the right values */
        $user = new $role($id, $username, $first, $last, $email, $avatar);

        return $user;
    }
}

?>
