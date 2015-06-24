<?php
/**
 * \brief Validate registration data.
 * @author Martino Pilia <martino.pilia@gmail.com>
 * @date 2015-06-24
 *
 * This script validates data from a registration form.
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
require __DIR__ . "/../settings.php";

/**
 * \brief Validate user data.
 * @param req The _REQUEST array.
 * @return An associative array containing the validated data or the error
 * messages related to invalid values.
 */
function validate(&$req) {
    require __DIR__ . "/../settings.php";

    /* a valid name must be composed by words starting with an uppercase
     * and lowercase for the rest. There must be at least one word, other
     * words must be separated by space.
     * This regex supports unicode characters. */
    $namePattern = '/^\s*\p{Lu}\p{Ll}+(\s+\p{Lu}\p{Ll}+)*\s*$/u';

    /* variables for values and error messages */
    $username = Null;
    $password = Null;
    $repeatedPassword = Null;
    $email = Null;
    $first = Null;
    $last = Null;
    $avatar = Null;
    $wrongUsername = Null;
    $wrongPassword = Null;
    $wrongRepeatedPassword = Null;
    $wrongEmail = Null;
    $wrongFirst = Null;
    $wrongLast = Null;
    $wrongImage = Null;

    /* test username */
    if (!isset($req['username']) || $req['username'] == "")
        $wrongUsername = "Username required";
    elseif (!is_string($req['username']))
        $wrongUsername = "Invalid username";
    else
    {
        $username = trim($req['username']);

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
            $mysqli = Database::getInstance()->connect(); /* db connection */
            Database::checkConnection($mysqli); /* check db connection */
            $stmt = $mysqli->stmt_init();
            $stmt->prepare($query);
            $stmt->bind_param("s", $username);
            $stmt->execute();
            Database::checkStmt($stmt, $mysqli); /* error handler */
            $stmt->bind_result($matching);
            $stmt->fetch();
            $stmt->close();
            $mysqli->close(); /* close db connection */

            if ($matching == 1) {
                $wrongUsername = "Username already in use";
            }

            if ($matching > 1) { /* many users with the same username found */
                Database::dbCorruption();
                exit();
            }
        }
    }

    /* test password */
    if (!isset($req['password']) || $req['password'] == "")
        $wrongPassword = "Password required";
    elseif (!is_string($req['password']))
        $wrongPassword = "Invalid password";
    else
    {
        $password = $req['password'];
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
    if (
            !isset($req['password-rep']) ||
            $req['password-rep'] == "" ||
            !is_string($req['password-rep']))
        $wrongRepeatedPassword = "Password does not match";
    else
    {
        $repeatedPassword = $req['password-rep'];
        if (strcmp($repeatedPassword, $password))
            $wrongRepeatedPassword = "Password does not match";
    }

    /* test email */
    if (!isset($req['email']) || $req['email'] == "")
        $wrongEmail = "E-mail required";
    elseif (!is_string($req['email']))
        $wrongEmail = "Seems not a valid e-mail address";
    else {
        $email = filter_var($req['email'], FILTER_VALIDATE_EMAIL);
        /* check length */
        if(strlen($email) >= $maxEmailLen)
            $wrongEmail = "Must be less than $maxEmailLen characters long";
        elseif ($email === FALSE)
            $wrongEmail = "Seems not a valid e-mail address";
    }

    /* test first name */
    if (!isset($req['first']) || $req['first'] == "")
        $wrongFirst = "First name required";
    elseif(!is_string($req['first']))
        $wrongFirst = "Seems not a valid first name";
    else {

        $first = trim($req['first']);
        $first = preg_replace('/\s+/', ' ', $first); /* trim multiple spaces */

        /* check length */
        if(strlen($first) >= $maxNameLen)
            $wrongFirst = "Must be less than $maxNameLen characters long";

        /* check if the name format is valid */
        elseif (!preg_match($namePattern, $first))
            $wrongFirst = "Must be a sequence of capitalized words";
    }

    /* test last name */
    if (!isset($req['last']) || $req['last'] == "")
        $wrongLast = "Last name required";
    elseif(!is_string($req['last']))
        $wrongLast = "Must be a sequence of capitalized words";
    else {
        $last = trim($req['last']);
        $last = preg_replace('/\s+/', ' ', $last); /* trim multiple spaces */

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
?>
