<?php
/**
 * \brief Settings changes validation and application.
 * @author Martino Pilia <martino.pilia@gmail.com>
 * @date 2015-05-14
 *
 * This script validates data from the settings page form. This works both for
 * an AJAX request for client side validation before submission (returning the
 * answer and eventual error messages in JSON format) and for a setting
 * submission (updating the database entry for the user and the user object in
 * the current session).
 */

/* document root */
$root = $_SERVER['DOCUMENT_ROOT'];
/* ensure there is the final slash */
if (substr($root, -1) != "/")
    $root .= "/";
/* http hostname */
$host  = $_SERVER['HTTP_HOST'];
/* protocol */
if (isset($_SERVER['HTTPS']) && strtoupper($_SERVER['HTTPS']) == 'ON')
    $protocol = "https";
else
    $protocol = "http";

require_once $root . "/php/model/User.php";
require_once $root . "/php/view/ViewDescriptor.php";
require_once $root . "/php/Database.php";
require_once __DIR__ . "/dataValidator.php";
require $root . "/php/settings.php";

if (!session_id())
    session_start();

/* validate new data */
$result = validate($_REQUEST);

/* create variables with validated data or error messages */
extract($result);

if ($wrongPassword || $wrongRepeatedPassword) {
    /* something is invalid: redirect to the settings page, with errors
     * highlighted */
    $vd = new ViewDescriptor();
    $vd->setTitle("Settings");
    $vd->setPage(ViewDescriptor::$settings);
    include_once $vd->getRoot() . '/php/view/master.php';
    exit();
}

/* compute password hash */
if ($password)
$passwordHash = password_hash($password, PASSWORD_DEFAULT, $options);

/* change password in the database */
$id = $_SESSION['user']->getId();
$query = "UPDATE Users SET Password_hash = ? WHERE Id = $id";
$mysqli = Database::getInstance()->connect(); /* db connection */
Database::checkConnection($mysqli); /* check db connection */

/* save user data into the database */
$stmt = $mysqli->stmt_init();
$stmt->prepare($query);
$stmt->bind_param("s", $passwordHash);
$stmt->execute();
Database::checkStmt($stmt, $mysqli); /* error handler */
$stmt->close();
$mysqli->close(); /* close db connection */

/* return to settings, with a confirmation message */
$vd = new ViewDescriptor();
$vd->setTitle("Settings");
$vd->setPage(ViewDescriptor::$settings, $_SESSION['user']->getRole());
$confirmationMessage =
        "<div class=\"confirm-message\">Password successfully changed.</div>";
include_once $vd->getRoot() . '/php/view/master.php';

exit();
?>
