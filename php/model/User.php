<?php

include_once __DIR__ . "/../model/Database.php";
include_once __DIR__ . "/Admin.php";
include_once __DIR__ . "/Enum.php";
include_once __DIR__ . "/Role.php";

/**
 * \brief Exception raised when the database entry for an user seems invalid.
 *
 * @author Martino Pilia <martino.pilia@gmail.com>
 * @date 2015-06-11
 */
class InvalidUserException extends Exception {
    public function __construct($msg) {
        parent::__construct($msg);
    }
}

/**
 * \brief Class defining a generic user.
 *
 * @author Martino Pilia <martino.pilia@gmail.com>
 * @date 2015-04-29
 */
class User {
    private $id;              /**< User Id in the database. */
    private $username;        /**< Username. */
    private $first;           /**< First name. */
    private $last;            /**< Last name. */
    private $email;           /**< E-mail. */
    private $avatar;          /**< Avatar image file. */
    protected $role;          /**< User's role. */

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

    /**
     * \brief Set user role, and update the value in the database.
     * @param string $role The role for the user.
     * @throws InvalidArgumentException when the parameter is invalid.
     */
    public function setRole($role) {
        if (!Role::isValid($role))
            throw new InvalidArgumentException("Invalid role \"$role\"");

        if ($role == $this->role)
            return;

        /* open db connection */
        $mysqli = Database::getInstance()->connect();
        $stmt = $mysqli->stmt_init();
        $stmt->prepare("UPDATE Users SET Role = ? WHERE Id = ?");
        if (!$stmt)
            throw new DbQueryException($stmt->errno,
                    $stmt->error, "Stmt prepare");

        if (!$stmt->bind_param("sd", $role, $this->id))
            throw new DbQueryException($stmt->errno,
                    $stmt->error, "Stmt bind param");

        if (!$stmt->execute())
            throw new DbQueryException($stmt->errno,
                    $stmt->error, "Stmt execute");

        $stmt->close();
        $mysqli->close(); /* close db connection */

        /* set attribute in the object */
        $this->role = $role;
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
     * \brief Remove an user from the database, along with all his events,
     * 		  and delete his avatar image file.
     */
    public function deleteUser() {
        require __DIR__ . "/../settings.php";

        /* delete the user from the database, along with all his events,
         * with a database transaction */
        $query1 = "DELETE FROM Events WHERE Owner_id = ?";
        $query2 = "DELETE FROM Users WHERE Id = ?";

        $mysqli = Database::getInstance()->connect();

        /* init, prepare and bind stmt1 */
        $stmt1 = $mysqli->stmt_init();
        $stmt1->prepare($query1);
        if (!$stmt1)
            throw new DbQueryException($stmt1->errno,
                    $stmt1->error, "Stmt prepare");
        $bind = $stmt1->bind_param("d", $this->id);
        if (!$bind)
            throw new DbQueryException($stmt1->errno,
                    $stmt1->error, "Stmt bind param");

        /* init, prepare and bind stmt2 */
        $stmt2 = $mysqli->stmt_init();
        $stmt2->prepare($query2);
        if (!$stmt2)
            throw new DbQueryException($stmt2->errno,
                    $stmt2->error, "Stmt prepare");
        $bind = $stmt2->bind_param("d", $this->id);
        if (!$bind)
            throw new DbQueryException($stmt2->errno,
                    $stmt2->error, "Stmt bind param");

        /* start transaction */
        $mysqli->autocommit(False);

        if (!$stmt1->execute()) {
            $mysqli->rollback();
            throw new DbQueryException($stmt1->errno,
                    $stmt1->error, "Stmt execute");
        }
        if (!$stmt2->execute()) {
            $mysqli->rollback();
            throw new DbQueryException($stmt2->errno,
                    $stmt2->error, "Stmt execute");
        }

        /* all seems ok, commit changes to the database */
        if (!$mysqli->commit()) {
            $mysqli->rollback();
            throw new DbQueryException($mysqli->errno,
                    $mysqli->error, "Commit");
        }

        /* reset autocommit and release resources */
        $stmt1->close();
        $stmt2->close();
        $mysqli->autocommit(True);
        $mysqli->close();

        /* delete user avatar personal file, if present*/
        if ($this->avatar != $defaultAvatar)
            unlink($this->avatar);
    }

    /**
     * \brief Get the number of events created by the user from the database.
     * @return An integer representing the number of events created by the user.
     * @note The value is not memorized in the object state because it may
     *       change during the session.
     */
    public function getCreatedEvents() {
        /* variable for the result */
        $createdEvents = 0;

        /* open db connection */
        $mysqli = Database::getInstance()->connect();
        $stmt = $mysqli->stmt_init();
        $stmt->prepare("SELECT Created_events FROM Users WHERE Id = ?");
        if (!$stmt)
            throw new DbQueryException($stmt->errno,
                    $stmt->error, "Stmt prepare");

        if (!$stmt->bind_param("d", $this->id))
            throw new DbQueryException($stmt->errno,
                    $stmt->error, "Stmt bind param");

        if (!$stmt->execute())
            throw new DbQueryException($stmt->errno,
                    $stmt->error, "Stmt execute");

        $bind = $stmt->bind_result($createdEvents);
        if (!$bind)
            throw new DbQueryException($stmt->errno,
                    $stmt->error, "Stmt bind result");

        $stmt->fetch();
        $stmt->close();
        $mysqli->close(); /* close db connection */

        return $createdEvents;
    }

    /**
     * \brief Return user id.
     * @return An integer containing the user's id.
     */
    public function getId() {
        return $this->id;
    }

    /**
     * \brief Get all users from the database.
     * @return array An array of User objects.
     * @throws InvalidUserException when the database contains an user with an
     *         invalid role.
     */
    public static function getUsers() {
        /* variable for the result */
        $users = array();

        /* open db connection */
        $mysqli = Database::getInstance()->connect();
        $query = "SELECT Id, Username, Email, Avatar, First, Last, Role
                  FROM Users";
        $result = $mysqli->query($query);
        if ($mysqli->errno > 0)
            throw new DbQueryException($mysqli->errno,
                    $mysqli->error, "Query error");

        while ($row = $result->fetch_object()) {
            $id = $row->Id;
            $username = $row->Username;
            $email = $row->Email;
            $avatar = $row->Avatar;
            $first = $row->First;
            $last = $row->Last;
            $role = $row->Role;

            if (!Role::isValid($role))
                throw new InvalidUserException(
                        "The user with id $id has an invalid role.");

            /* create an object of the right class for the user,
             * and fill it with the right values */
            $users[] = new $role(
                    $id,
                    $username,
                    $first,
                    $last,
                    $email,
                    $avatar);
        }
        $mysqli->close(); /* close db connection */

        return $users;
    }

    /**
     * \brief Create a user object extracting its data from the database.
     * @param $id User id.
     * @return An object defining the user.
     * @throws InvalidUserException When the database entry for a user seems
     *         invalid.
     *         InvalidArgumentException When the $id parameter is invalid.
     */
    public static function newUserById($id) {
        if (!isset($id) || !filter_var($id, FILTER_VALIDATE_INT))
            throw new InvalidArgumentException("Missing or invalid parameter.");

        $mysqli = Database::getInstance()->connect(); /* open db connection */
        $stmt = $mysqli->stmt_init();
        $stmt->prepare("SELECT Username, Email, Avatar, First, Last, Role
                        FROM Users WHERE Id = ?");
        if (!$stmt)
            throw new DbQueryException($stmt->errno,
                    $stmt->error, "Stmt prepare");

        if (!$stmt->bind_param("d", $id))
            throw new DbQueryException($stmt->errno,
                    $stmt->error, "Stmt bind param");

        if (!$stmt->execute())
            throw new DbQueryException($stmt->errno,
                    $stmt->error, "Stmt execute");

        $bind = $stmt->bind_result(
                $username,
                $email,
                $avatar,
                $first,
                $last,
                $role);
        if (!$bind)
            throw new DbQueryException($stmt->errno,
                    $stmt->error, "Stmt bind result");

        $stmt->fetch();
        $stmt->close();
        $mysqli->close(); /* close db connection */

        /* ensure the role is valid */
        if (!Role::isValid($role))
            throw new InvalidUserException(
                    "The user with id $id has an invalid role.");

        /* create an object of the right class for the user, and fill it with
         * the right values */
        $user = new $role($id, $username, $first, $last, $email, $avatar);

        return $user;
    }

    /**
     * \brief Check if the user credentials are valid and return the user id.
     * @param  string $username Input username.
     * @param  string $password Input password.
     * @return array  An array containing ($userProblem, $passProblem, $id).
     *                When the credentials are invalid, the first two are
     *                strings containing the error messages, when they are valid
     *                the first two are Null and the third field contains the
     *                id of the matching user.
     */
    public static function checkUser($username, $password) {
        /* variables for the result */
        $userProblem = Null;
        $passProblem = Null;
        $id = Null; /* variable for the user id */

        /* check username */
        if (!$username)
            $userProblem = "Username required";
        elseif (!preg_match('/[A-Za-z0-9_-]+/', $username))
            $userProblem = "Username seems invalid";

        /* check password */
        if (!$password)
            $passProblem = "Password required";

        /* missing or clearly invalid credentials, do not check in the db */
        if ($userProblem || $passProblem)
            return array($userProblem, $passProblem, Null);

        /* check the user in the db */
        $mysqli = Database::getInstance()->connect();

        $hash = ""; /* variable for the password hash */
        $result = 0; /* number of matching rows in the user table */

        $query = "SELECT Id, Password_hash
                  FROM Users
                  WHERE Username = ?";

        $stmt = $mysqli->stmt_init();
        $stmt->prepare($query);
        if (!$stmt)
            throw new DbQueryException($stmt->errno,
                    $stmt->error, "Stmt prepare");

        if (!$stmt->bind_param("s", $username))
            throw new DbQueryException($stmt->errno,
                    $stmt->error, "Stmt bind param");

        if (!$stmt->execute())
            throw new DbQueryException($stmt->errno,
                    $stmt->error, "Stmt execute");

        if (!$stmt->bind_result($id, $hash))
            throw new DbQueryException($stmt->errno,
                    $stmt->error, "Stmt bind result");

        while ($stmt->fetch()) /* count results */
            $result++;

        $stmt->close();
        $mysqli->close(); /* close db connection */

        /* many users with the same username found */
        if ($result > 1)
            throw new DbCorruptionException(
                    "Many users with the username \"$username\"");

        /* if username is not registered */
        if ($result == 0)
            $userProblem = "Seems not a registered username";

        /* valid username, check the input password against the hash */
        else if (password_verify($password, $hash)) {
            /* valid password */
            /* Ensure the hash algorithm and options are not obsolete */
            if (password_needs_rehash($hash, PASSWORD_DEFAULT,
                    $options)) {
                $newHash = password_hash($password, PASSWORD_DEFAULT,
                        $options);

                /* write newHash in the db */
                $mysqli = Database::getInstance()->connect();

                $query = "UPDATE Users SET Password_hash = ?
                          WHERE Username = ?";
                $stmt = $mysqli->stmt_init();
                $stmt->prepare($query);

                if (!$stmt)
                    throw new DbQueryException(Null, Null, "Stmt init");

                $bind = $stmt->bind_param("ss", $newHash, $username);
                if (!$bind)
                    throw new DbQueryException($stmt->errno,
                            $stmt->error, "Stmt bind param");

                if (!$stmt->execute())
                    throw new DbQueryException($stmt->errno,
                            $stmt->error, "Stmt execute");

                $stmt->close();
                $mysqli->close(); /* close db connection */
            }
        }
        else {
            /* wrong password */
            $passProblem = "Wrong password";
        }

        return array($userProblem, $passProblem, $id);
    }

    /**
     * \brief Create a user entry in the database and return a user entry.
     * @param  string $username Username for the user.
     * @param  string $password Password for the user.
     * @param  string $email    Email for the user.
     * @param  string $first    First name for the user.
     * @param  string $last     Last name for the user.
     * @param  string $avatar   Path of the image file for the user.
     * @return User   An object for the registered user.
     */
    public static function registerUser($username, $password, $email,
            $first, $last, $avatar) {
        require __DIR__ . "/../settings.php";

        /* if the user has uploaded an avatar image, save it */
        if ($avatar != $defaultAvatar) {
            /* determine a random name for the destination
             * avatar file */
            do {
                $avatar =
                    $uploadDir
                    . basename($_FILES['avatar']['tmp_name'])
                    . mt_rand(10000000, 99999999);
            } while (file_exists($avatar)); /* ensure the filename
                                               is not in use */

            /* move the uploaded file to its destination */
            $dest = __DIR__ . "/../../" . $avatar;
            if (move_uploaded_file($_FILES['avatar']['tmp_name'],
                    $dest) === False) {
                /* error moving the file: log the error and use
                 * the default avatar as fallback */
                $avatar = $defaultAvatar;
            }
        }

        /* compute password hash */
        $passwordHash = password_hash($password, PASSWORD_DEFAULT, $options);

        /* add the user to the database */
        $role = Role::USER;
        $query = "INSERT INTO Users "
            . "(Username, Password_hash, Email, Avatar, First, Last, Role) "
            . "VALUES "
            . "(?, ?, ?, ?, ?, ?, ?)";
        $mysqli = Database::getInstance()->connect(); /* db connection */

        /* save user data into the database */
        $stmt = $mysqli->stmt_init();
        $stmt->prepare($query);
        if (!$stmt)
            throw new DbQueryException($stmt->errno,
                    $stmt->error, "Stmt prepare");

        $bind = $stmt->bind_param(
            "sssssss",
            $username,
            $passwordHash,
            $email,
            $avatar,
            $first,
            $last,
            $role);
        if (!$bind)
            throw new DbQueryException($stmt->errno,
                    $stmt->error, "Stmt bind param");

        if (!$stmt->execute())
            throw new DbQueryException($stmt->errno,
                    $stmt->error, "Stmt execute");

        $stmt->close();

        /* retrieve user id */
        $id = 0;
        $stmt = $mysqli->stmt_init();
        $stmt->prepare("SELECT Id FROM Users WHERE Username = ?");
        if (!$stmt)
            throw new DbQueryException($stmt->errno,
                    $stmt->error, "Stmt prepare");

        $bind = $stmt->bind_param("s", $username);
        if (!$bind)
            throw new DbQueryException($stmt->errno,
                    $stmt->error, "Stmt bind param");

        if (!$stmt->execute())
            throw new DbQueryException($stmt->errno,
                    $stmt->error, "Stmt execute");

        $bind = $stmt->bind_result($id);
        if (!$bind)
            throw new DbQueryException($stmt->errno,
                    $stmt->error, "Stmt bind result");

        $stmt->fetch();
        $stmt->close();
        $mysqli->close(); /* close db connection */

        return new User($id, $username, $first, $last, $email, $avatar);
    }

    /**
     * \brief Get the user id and the email of the owner of a reset token.
     * @param string $token Reset token.
     * @return array Associative array containing (id, email) for the
     *               owner, or Null if not found.
     */
    public static function getResetTokenOwner($token) {
        $id = Null;
        $email = Null;

        /* search the reset token in the database */
        $mysqli = Database::getInstance()->connect();
        $stmt = $mysqli->stmt_init();
        $stmt->prepare("SELECT Id, Email
                       FROM Users
                       WHERE ResetToken = ?");
        if (!$stmt)
            throw new DbQueryException($stmt->errno,
                    $stmt->error, "Stmt prepare");

        $bind = $stmt->bind_param("s", $token);
        if (!$bind)
            throw new DbQueryException($stmt->errno,
                    $stmt->error, "Stmt bind param");

        if (!$stmt->execute())
            throw new DbQueryException($stmt->errno,
                    $stmt->error, "Stmt execute");

        $bind = $stmt->bind_result($id, $email);
        if (!$bind)
            throw new DbQueryException($stmt->errno,
                    $stmt->error, "Stmt bind result");

        while ($stmt->fetch())
            $matches++;

        if ($matches > 1)
            throw new DbCorruptionException(
                 "Many users with the same reset token $token");

        $stmt->close();
        $mysqli->close(); /* close db connection */

        if ($id == Null || $email == Null)
            return Null;
        else
            return array($id, $email);
    }

    /**
     * \brief Change the user's password and set the reset token to NULL.
     * @param  int    $id          User id.
     * @param  string $newPassword New password for the user.
     */
    public static function passwordChange($id, $newPassword) {
        require __DIR__ . "/../settings.php";

        if (!isset($id) || !filter_var($id, FILTER_VALIDATE_INT))
            throw new InvalidArgumentException("Invalid id value \"$id\"");
        if (!isset($newPassword))
            throw new InvalidArgumentException("Password parameter missing");

        /* compute password hash */
        $hash = password_hash($newPassword, PASSWORD_DEFAULT, $options);

        $mysqli = Database::getInstance()->connect();
        $stmt = $mysqli->stmt_init();
        $stmt->prepare("UPDATE Users
                        SET ResetToken = NULL,
                            Password_hash = ?
                        WHERE Id = ?");
        if (!$stmt)
            throw new DbQueryException($stmt->errno,
                    $stmt->error, "Stmt prepare");

        $bind = $stmt->bind_param("sd", $hash, $id);
        if (!$bind)
            throw new DbQueryException($stmt->errno,
                    $stmt->error, "Stmt bind param");

        if (!$stmt->execute())
            throw new DbQueryException($stmt->errno,
                    $stmt->error, "Stmt execute");

        $stmt->close();
        $mysqli->close(); /* close db connection */
    }

    /**
     * \brief Generate a reset token for the user.
     * @param  string $username Username for the user.
     * @return array  An associative array, containing (id, email, token),
     *                or Null if the user has not been found.
     * @throws InvalidArgumentException When a parameter is invalid.
     * @note This function does not save the reset token in the database.
     */
    public static function generateTokenForUser($username) {
        if (!isset($username))
            throw new InvalidArgumentException("Missing username parameter");

        /* variables for result */
        $id = Null;
        $email = Null;

        $mysqli = Database::getInstance()->connect();

        /* get user id and email for the username */
        $stmt = $mysqli->stmt_init();
        $stmt->prepare("SELECT Id, Email FROM Users WHERE Username = ?");
        if (!$stmt)
            throw new DbQueryException($stmt->errno,
                    $stmt->error, "Stmt prepare");

        $bind = $stmt->bind_param("s", $username);
        if (!$bind)
            throw new DbQueryException($stmt->errno,
                    $stmt->error, "Stmt bind param");

        if (!$stmt->execute())
            throw new DbQueryException($stmt->errno,
                    $stmt->error, "Stmt execute");

        $bind = $stmt->bind_result($id, $email);
        if (!$bind)
            throw new DbQueryException($stmt->errno,
                    $stmt->error, "Stmt bind result");

        $matches = 0;
        while ($stmt->fetch())
            $matches++;

        if ($matches > 1)
             throw new DbCorruptionException(
                     "Many users with the username $username");

        $stmt->close();

        if (!$matches) {
            /* user not found */
            $mysqli->close();
            return Null;
        }

        /* Create a token and ensure it is not present in the database yet.
         * The token is the hash of a string containing a timestamp and
         * a random number. A collision may happen with a probability
         * smaller than 1e-10.
         */
        do {
            $token = md5(mt_rand(1000000000, 9999999999) . time());
            $result = $mysqli->query("SELECT COUNT(ResetToken)
                                      FROM Users
                                      WHERE ResetToken = \"$token\"");
            if ($mysqli->errno > 0)
                throw new DbQueryException($mysqli->errno,
                        $mysqli->error, "Query error");

            $row = $result->fetch_row();
        } while ($row[0] != 0);

        $mysqli->close(); /* close db connection */

        return array($id, $email, $token);
    }

    /**
     * \brief Save the reset token for a user in the database.
     * @param int    $id    User id.
     * @param string $token Reset token for the user.
     * @throws InvalidArgumentException When a parameter is invalid.
     */
    public static function setResetToken($id, $token) {
        if (!isset($id) || !filter_var($id, FILTER_VALIDATE_INT))
            throw new InvalidArgumentException("Invalid id value \"$id\"");
        if (!isset($token))
            throw new InvalidArgumentException("Token parameter missing");

        $mysqli = Database::getInstance()->connect();
        $stmt = $mysqli->stmt_init();
        $stmt->prepare("UPDATE Users SET ResetToken = ? WHERE Id = ?");
        if (!$stmt)
            throw new DbQueryException($stmt->errno,
                    $stmt->error, "Stmt prepare");

        $bind = $stmt->bind_param("sd", $token, $id);
        if (!$bind)
            throw new DbQueryException($stmt->errno,
                    $stmt->error, "Stmt bind param");

        if (!$stmt->execute())
            throw new DbQueryException($stmt->errno,
                    $stmt->error, "Stmt execute");

        $stmt->close();
        $mysqli->close(); /* close db connection */
    }

    /**
     * \brief Validate user data.
     * @param string $username User username.
     * @param string $first    User first name.
     * @param string $last     User last name.
     * @param string $email    User email address.
     * @param string $avatar   User avatar filename.
     * @return An associative array containing the validated data or the error
     * messages related to invalid values.
     */
    public static function validateUserData($username, $password,
            $repeatedPassword, $email, $first, $last, $avatar) {
        require __DIR__ . "/../settings.php";

        /* trim whitespace at begin and end */
        $username = trim($username);
        $email = trim($email);
        $first = trim($first);
        $last = trim($last);

        /* trim multiple spaces */
        $first = preg_replace('/\s+/', ' ', $first);
        $last = preg_replace('/\s+/', ' ', $last);

        /* a valid name must be composed by words starting with an uppercase
         * and lowercase for the rest. There must be at least one word, other
         * words must be separated by space.
         * This regex supports unicode characters. */
        $namePattern = '/^\s*\p{Lu}\p{Ll}+(\s+\p{Lu}\p{Ll}+)*\s*$/u';

        /* variables for values and error messages */
        $wrongUsername = Null;
        $wrongPassword = Null;
        $wrongRepeatedPassword = Null;
        $wrongEmail = Null;
        $wrongFirst = Null;
        $wrongLast = Null;
        $wrongImage = Null;

        /* test username */
        if (!$username)
            $wrongUsername = "Username required";
        else
        {
            /* check username validity */
            if (!preg_match('/^[A-Za-z0-9_-]+$/', $username)) {
                $wrongUsername =
                        "Must contain A-Z, a-z, '_' and '-' characters only";
            }
            /* check username length */
            elseif(strlen($username) >= $maxUsernameLen) {
                $wrongUsername =
                        "Must be less than $maxUsernameLen characters long";
            }
            /* check username availability */
            else {
                $matching = 0; /* matches in the user table for the username */
                $query = "SELECT COUNT(Username) FROM Users WHERE Username=?";
                $mysqli = Database::getInstance()->connect();
                $stmt = $mysqli->stmt_init();
                $stmt->prepare($query);
                if (!$stmt)
                    throw new DbQueryException($stmt->errno,
                            $stmt->error, "Stmt prepare");

                $bind = $stmt->bind_param("s", $username);
                if (!$bind)
                    throw new DbQueryException($stmt->errno,
                            $stmt->error, "Stmt bind param");

                if (!$stmt->execute())
                    throw new DbQueryException($stmt->errno,
                            $stmt->error, "Stmt execute");

                $bind = $stmt->bind_result($matching);
                if (!$bind)
                    throw new DbQueryException($stmt->errno,
                            $stmt->error, "Stmt bind result");

                $stmt->fetch();

                $stmt->close();
                $mysqli->close(); /* close db connection */

                if ($matching == 1) {
                    $wrongUsername = "Username already in use";
                }

                if ($matching > 1)
                    throw new DbCorruptionException(
                            "Many users with the username $username");
            }
        }

        /* test password */
        if (!$password)
            $wrongPassword = "Password required";
        else
        {
            if (strlen($password) < $minPasswordLength)
                $wrongPassword = "Password must be at least "
                    . "$minPasswordLength characters long";
            elseif (
                    !preg_match('/[A-Z]/', $password) ||
                    !preg_match('/[0-9]/', $password))
                $wrongPassword = "Password must contain at least "
                    . "1 uppercase and 1 digit";
        }

        /* test password repetition */
        if (strcmp($repeatedPassword, $password))
            $wrongRepeatedPassword = "Password does not match";

        /* test email */
        if (!$email)
            $wrongEmail = "E-mail required";
        else {
            $email = filter_var($email, FILTER_VALIDATE_EMAIL);
            /* check length */
            if(strlen($email) >= $maxEmailLen)
                $wrongEmail = "Must be less than $maxEmailLen characters long";
            elseif ($email === FALSE)
                $wrongEmail = "Seems not a valid e-mail address";
        }

        /* test first name */
        if (!$first)
            $wrongFirst = "First name required";
        else {
            /* check length */
            if(strlen($first) >= $maxNameLen)
                $wrongFirst = "Must be less than $maxNameLen characters long";

            /* check if the name format is valid */
            elseif (!preg_match($namePattern, $first))
                $wrongFirst = "Must be a sequence of capitalized words";
        }

        /* test last name */
        if (!$last)
            $wrongLast = "Last name required";
        else {
            /* check length */
            if(strlen($last) >= $maxNameLen)
                $wrongLast = "Must be less than $maxNameLen characters long";

            /* see above */
            elseif (!preg_match($namePattern, $last))
                $wrongLast = "Must be a sequence of capitalized words";
        }

        /* test avatar */
        if(isset($_FILES['avatar']) && $_FILES['avatar']['name'] != "") {

            $imageData = getimagesize($_FILES['avatar']['tmp_name']);

            /* ensure it is an image and check type */
            if ($imageData === FALSE) {
                $wrongImage = "Seems not a valid image";
            }
            elseif (
                    $imageData[2] != IMAGETYPE_GIF &&
                    $imageData[2] != IMAGETYPE_JPEG &&
                    $imageData[2] != IMAGETYPE_PNG) {
                $wrongImage = "Supported formats are .jpg/.jpeg, .png, .gif";
            }

            /* check file size */
            elseif ($_FILES['avatar']['size'] > 1000 * $maxImageSize) {
                $wrongImage = "Image size must be < $maxImageSize kB";
            }
        } else {
            /* use default avatar */
            $avatar = $defaultAvatar;
        }

        /* return an associative array, containing the value of the validated
         * fields or the error messages for invalid data */
        return array(
                'wrongUsername' => $wrongUsername,
                'wrongPassword' => $wrongPassword,
                'wrongRepeatedPassword' => $wrongRepeatedPassword,
                'wrongEmail' => $wrongEmail,
                'wrongFirst' => $wrongFirst,
                'wrongLast' => $wrongLast,
                'wrongImage' => $wrongImage,
                'username' => $username,
                'password' => $password,
                'email' => $email,
                'first' => $first,
                'last' => $last,
                'avatar' => $avatar,
                'allValid' => !($wrongUsername ||
                                $wrongPassword ||
                                $wrongRepeatedPassword ||
                                $wrongEmail ||
                                $wrongFirst ||
                                $wrongLast ||
                                $wrongImage));
    }

    /**
     * \brief Return all the events owned by the user during the next week.
     * @return An associative array, whose keys are strings in the form
     *         'yyyy/mm/dd' (to have an equivalence between chronological
     *         and lexicographic sort), whose values are EventSet objects
     *         containing the events for each date.
     */
    public function getNextWeekEvents() {
        /* container for the events */
        $events = array();

        /* open db connection */
        $mysqli = Database::getInstance()->connect();

        /* get the events in the next week, i.e. 7 days from today */
        for ($i = 0; $i <= 7; $i++) {
            /* get the date of the $i-th day from now */
            $date = date("Y/m/d", strtotime("now + $i days"));
            list($year, $month, $day) = explode('/', $date);

            /* container for the events for the specific day */
            $dayEvents = new EventSet();

            /* safe conversion for the query */
            $id = (int) $this->id;

            $query = "SELECT
                          Events.Id,
                          Start,
                          End,
                          Resources.Id,
                          Resources.Name,
                          Notes
                      FROM Events
                      JOIN Users ON Events.Owner_id = Users.Id
                      JOIN Resources ON Events.Resource_id = Resources.Id
                      WHERE
                          Day = $day AND
                          Month = $month AND
                          Year = $year AND
                          Owner_id = $id";

            $result = $mysqli->query($query);
            if ($mysqli->errno > 0) {
                $mysqli->close();
                throw new DbQueryException($mysqli->errno,
                        $mysqli->error, "Query error");
            }

            while ($row = $result->fetch_row()) {
                $e = new Event(
                        $row[0],
                        $this->username,
                        $day,
                        $month,
                        $year,
                        $row[1],
                        $row[2],
                        new Resource($row[3], $row[4]),
                        $this->role,
                        $row[5]);

                /* add this day to the result array
                 * for today's events (i.e. $i == 0) check that they are not
                 * finished yet */
                if ($i == 0) {
                    /* convert end time in the format hh:mm */
                    $endTime = sprintf("%02d", floor($e->getEnd() / 2)) . ":"
                             . ($e->getEnd() % 2 == 0 ? "00" : "30");
                    $currentHour = date("H:i"); /* get current hour */

                    if (strcmp($endTime, $currentHour) > 0)
                        /* the event is not finished yet */
                        $dayEvents->addEvent($e);
                }
                else
                    /* future event */
                    $dayEvents->addEvent($e);
            }


            $events["$date"] = $dayEvents;
        }

        /* close db connection */
        $mysqli->close();

        /* sort array by keys */
        ksort($events);

        return $events;
    }
}

?>
