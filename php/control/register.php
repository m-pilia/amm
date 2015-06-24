<?php
/**
 * \brief Validate registration data.
 * @author Martino Pilia <martino.pilia@gmail.com>
 * @date 2015-05-14
 *
 * This script validates data from a registration form. This works both for an
 * AJAX request for client side validation before submission (returning the
 * answer and eventual error messages in JSON format) and for a registration
 * request (creating a database entry for the user, opening a session and
 * redirecting to the home page).
 */

/* http hostname */
$host  = $_SERVER['HTTP_HOST'];
/* protocol */
if (isset($_SERVER['HTTPS']) && strtoupper($_SERVER['HTTPS']) == 'ON')
    $protocol = "https";
else
    $protocol = "http";

require_once __DIR__ . "/../model/User.php";
require_once __DIR__ . "/../view/ViewDescriptor.php";
require_once __DIR__ . "/../Database.php";
require_once __DIR__ . "/dataValidator.php";
require __DIR__ . "/../settings.php";

if (!isset($ajaxAnswer))
    $ajaxAnswer = Null;

/* validate data */
$result = validate($_REQUEST);

/* create variables with validated data or error messages */
extract($result);

/* response to an AJAX request for client side validation */
if ($ajaxAnswer) {
    include __DIR__ . '/../view/default/registration/ajax.php';
    exit();
}

if (!$allValid) {
    /* registration failed: redirect to the registration page, with errors
     * highlighted */
    $vd = new ViewDescriptor();
    $vd->setTitle("Registration");
    $vd->setPage(ViewDescriptor::$registration, Null);
    include_once __DIR__ . '/../view/master.php';
    exit();
}

/* otherwise, registration data is valid */
if ($avatar != $defaultAvatar) { /* if the user has uploaded an avatar image */
    /* determine a random name for the destination avatar file */
    do {
        $avatar =
            $uploadDir
            . basename($_FILES['avatar']['tmp_name'])
            . mt_rand(10000000, 99999999);
    } while (file_exists($avatar)); /* check if file already exists */

    /* move the uploaded file to its destination */
    $dest = __DIR__ . "/../../" . $avatar;
    if (move_uploaded_file($_FILES['avatar']['tmp_name'], $dest) === FALSE) {
        /* error moving the file: log the error and use default avatar
         * as fallback */
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
Database::checkConnection($mysqli); /* check db connection */

/* save user data into the database */
$stmt = $mysqli->stmt_init();
$stmt->prepare($query);
$stmt->bind_param(
    "sssssss",
    $username,
    $passwordHash,
    $email,
    $avatar,
    $first,
    $last,
    $role);
$stmt->execute();
Database::checkStmt($stmt, $mysqli); /* error handler */
$stmt->close();

/* retrieve user id */
$id = 0;
$stmt = $mysqli->stmt_init();
$stmt->prepare("SELECT Id FROM Users WHERE Username = ?");
$stmt->bind_param("s", $username);
$stmt->execute();
Database::checkStmt($stmt, $mysqli); /* error handler */
$stmt->bind_result($id);
$stmt->fetch();
$stmt->close();
$mysqli->close(); /* close db connection */

/* open session */
if (!session_id())
    session_start();

/* create user object in the session array */
$_SESSION['user'] =
        new User($id, $username, $first, $last, $email, $avatar);

/* show registration confirmation page */
$vd = new ViewDescriptor();
$vd->setTitle("Registration success");
$vd->setPage(Null, Null);
$title = "Registration success";
$message = "Congratulations! You have completed the registration successfully, "
        . "and you will be redirected to the <a href=\"/home\">homepage</a> "
        . "in seconds.";
include_once __DIR__ . '/../view/master.php';

/* redirect to the homepage */
header("refresh: 5; url=$protocol://$host/home");

exit();
?>
