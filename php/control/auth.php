<?php
/**
 * \brief Manage auth
 * @author Martino Pilia <martino.pilia@gmail.com>
 * @date 2015-05-14
 *
 * This script manages the auth actions from the login page (login,
 * registration request, password reset).
 *
 * Login:
 *      The script verifies username and password against the database entries.
 *      If the credentials are valid, a session is opened for the user with the
 *      creation of a User object of the right class for his role. If the
 *      credentials are wrong, the user is redirected to the login page, with
 *      specific error messages.
 *
 * Registration:
 * 		If the user has requested the registration, [he|she]'s redirected to
 * 		the registration page, with the username field pre-filled if a username
 * 		has been provided in the login page along the registration request.
 *
 * Password reset:
 * 		This would require a working SMTP server on the local machine, or an
 * 		external PHP library to use a third party mail server.
 */

/* http hostname */
$host  = $_SERVER['HTTP_HOST'];
/* protocol */
if (isset($_SERVER['HTTPS']) && strtoupper($_SERVER['HTTPS']) == 'ON')
    $protocol = "https";
else
    $protocol = "http";

require_once __DIR__ . "/../model/User.php";
require_once __DIR__ . "/../Database.php";
require_once __DIR__ . "/FrontController.php";
require_once __DIR__ . "/../view/ViewDescriptor.php";
require_once __DIR__ . "/../settings.php";

if (!session_id())
    session_start();

/* login */
if (
        isset($_POST['login_button'])
        && !isset($_POST['register_button'])
        && !isset($_POST['reset_button']))
{
    /* error messages for missing/invalid username/password */
    $userProblem = Null;
    $passProblem = Null;

    /* check username */
    if (!isset($_POST['username'])) {
        $userProblem = "Username required";
    }
    elseif (!is_string($_POST['username'])) {
        $userProblem = "Username seems invalid";
    }
    else {
        $username = trim($_POST['username']);
        if (!$username)
            $userProblem = "Username required";
        elseif (!preg_match('/[A-Za-z0-9_-]+/', $username))
            $userProblem = "Username seems invalid";
    }

    /* check password */
    if (!isset($_POST['password'])) {
        $passProblem = "Password required";
    }
    elseif (!is_string($_POST['password'])) {
        $passProblem = "Password seems invalid";
    }
    else {
        $password = $_POST['password'];
        if (!$password)
            $passProblem = "Password required";
    }

    /* if at least one is missing/invalid, return to the login page
     * with an error indication */
    if ($userProblem || $passProblem) {
        FrontController::login($username, $userProblem, $passProblem);
        exit();
    }

    /* login data seems reasonable, check username in the database */
    $mysqli = Database::getInstance()->connect(); /* open db connection */
    Database::checkConnection($mysqli); /* check db connection */

    $id = 0; /* variable for the user id */
    $hash = ""; /* variable for the password hash */
    $result = 0; /* number of matching rows in the user table */

    $query = "SELECT Id, Password_hash FROM Users WHERE Username=?";
    $stmt = $mysqli->stmt_init();
    $stmt->prepare($query);
    $stmt->bind_param("s", $username);
    $stmt->execute();
    Database::checkStmt($stmt, $mysqli); /* error handler */
    $stmt->bind_result($id, $hash);
    while ($stmt->fetch()) /* count results */
        $result++;
    $stmt->close();
    $mysqli->close(); /* close db connection */

    /* more users with the same username found */
    if ($result > 1) {
        Database::dbCorruption();
        exit();
    }

    /* if username is not registered */
    if ($result == 0) {
        FrontController::login($username, "Seems not a registered username");
        exit();
    }

    /* check the input password against the hash */
    if (password_verify($password, $hash)) {

        /* Ensure the hash algorithm and options are not obsolete */
        if (password_needs_rehash($hash, PASSWORD_DEFAULT, $options)) {
            $newHash = password_hash($password, PASSWORD_DEFAULT, $options);

            /* write newHash in the db */
            $mysqli = Database::getInstance()->connect();
            Database::checkConnection($mysqli);

            $query = "UPDATE Users SET Password_hash=? WHERE Username=?";
            $stmt = $mysqli->stmt_init();
            $stmt->prepare($query);
            $stmt->bind_param("ss", $newHash, $username);
            $stmt->execute();
            Database::checkStmt($stmt, $mysqli); /* error handler */
            $stmt->close();
            $mysqli->close(); /* close db connection */
        }

        $hash = "";
        $password = "";

        /* Login successful: create user and redirect to the home.
         * NOTE: the method newUserById returns an object of the right class
         * according to the user's role. */
        try {
            $user = User::newUserById($id);
        } catch (InvalidUserException $e) {
            Database::dbCorruption();
        }
        $_SESSION[FrontController::USER] = $user;

        /* redirect to homepage */
        header("Location: $protocol://$host/home");
        exit();
    }

    /* login failed, wrong password */
    $hash = "";
    $password = "";
    FrontController::login($username, Null, "Wrong password");
    exit();
}

/* registration */
else if (
        isset($_POST['register_button'])
        && !isset($_POST['login_button'])
        && !isset($_POST['reset_button']))
{
    /* precompile registration form with eventual username */
    if (isset($_POST['username']) && is_string($_POST['username'])) {
        $username = trim($_POST['username']);
        unset($_POST['username']);
    }

    /* show registration page, with the username field pre-compiled */
    $vd = new ViewDescriptor();
    $vd->setTitle("Registration");
    $vd->setPage(ViewDescriptor::$registration, Null);

    include_once '../view/master.php';
    exit();
}

/* password reset */
else if (
        isset($_POST['reset_button'])
        && !isset($_POST['register_button'])
        && !isset($_POST['login_button']))
{
    /* error messages for missing/invalid username/password */
    $userProblem = Null;

    /* check username */
    if (!isset($_POST['username'])) {
        $userProblem = "Username required";
    }
    elseif (!is_string($_POST['username'])) {
        $userProblem = "Username seems invalid";
    }
    else {
        $username = trim($_POST['username']);
        if (!$username)
            $userProblem = "Username required";
        elseif (!preg_match('/[A-Za-z0-9_-]+/', $username))
            $userProblem = "Username seems invalid";
    }

    if ($userProblem) {
        FrontController::login($username, $userProblem, Null);
        exit();
    }

    /* redirect to reset page */
    header("Location: $protocol://$host/reset?username=$username");
}

/* invalid action */
else
{
    FrontController::write400();
    exit();
}

?>
