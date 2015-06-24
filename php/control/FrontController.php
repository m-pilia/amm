<?php

$root = $_SERVER['DOCUMENT_ROOT'];
$host  = $_SERVER['HTTP_HOST'];
require_once $root . "/php/model/User.php";
include_once $root . '/php/view/ViewDescriptor.php';
include_once $root . '/php/control/BaseController.php';
require_once $root . "/php/settings.php";

/**
 * \brief The application entry point, which sorts page requests.
 * @author Martino Pilia <martino.pilia@gmail.com>
 * @date 2015-04-29
 */
class FrontController {

    /**
     * Key for the page request in the array. Must match with the value
     * selected in the .htaccess configuration file.
     */
    const PAGE = "page";

    /**
     * Key for the command request in the array.
     */
    const CMD = "cmd";

    /**
     * Key for the user in the session array.
     */
    const USER = "user";

    /**
     * \brief Read the request and invoke the controller if it is legal,
     * write an error otherwise.
     * @param req Associative array containing the request.
     */
    public static function dispatch(&$req) {
        if (!session_id())
            session_start();

        if (!isset($req[self::PAGE])) {
            /* no page selected */
            self::write404();
        } else {
            /* page selected */
            switch ($req[self::PAGE]) {

                case "400": /* to allow Apache error redirection */
                    self::write400();
                    break;

                case "403": /* idem */
                    self::write403();
                    break;

                case "404": /* idem */
                    self::write404();
                    break;

                case "500": /* idem */
                    self::write500();
                    break;

                case "home": /* homepage */
                    self::home();
                    break;

                case "about": /* about page */
                    self::about();
                    break;

                case "login": /* login page */
                    self::login(Null, Null, Null);
                    break;

                case "logout": /* logout */
                    self::logout();
                    break;

                case "registration": /* registration page */
                    self::registration($req);
                    break;

                case "reset": /* password reset */
                    self::reset($req);
                    break;

                case "settings":
                    if (!isset($_SESSION[self::USER]))
                        self::write403("settings");
                    self::settings();
                    break;

                default: /* unrecognized page */
                    self::write404($req[self::PAGE]);
                    break;
            }
        }
    }

    /**
     * \brief Write a 400 error page.
     */
    public static function write400() {
        header('HTTP/1.0 400 Bad Request'); /* error response */
        /* error page components */
        $vd = new ViewDescriptor();
        $vd->setTitle("Error");
        $vd->setPage(ViewDescriptor::$error, Null);
        /* section title for error page */
        $title = "400 Bad Request.";
        /* error message */
        $message =  "The requested action seems invalid. Try returning to the "
                  . "<a href=\"javascript:history.back()\">previous page</a>.";
        $errorImage = "/images/broken.svg";

        include_once $vd->getRoot() . '/php/view/master.php';
        exit();
    }

    /**
     * \brief Write a 403 error page.
     */
    public static function write403($pagename) {
        header('HTTP/1.0 403 Forbidden'); /* error response */
        /* error page components */
        $vd = new ViewDescriptor();
        $vd->setTitle("Error");
        $vd->setPage(ViewDescriptor::$error, Null);
        /* section title for error page */
        $title = "403 Forbidden.";
        /* error message */
        $message =  "You don't have the needed permissions to access the page "
                  . (is_null($pagename) ? "" : "\"")  /* quotes if needed */
                  . $pagename
                  . (is_null($pagename) ? "" : "\" ")  /* close quotes, space */
                  . ". Try "
                  . "<a href=\"javascript:history.back()\">going back</a> "
                  . "or return to the <a href=\"/home\">homepage</a>.";
        $errorImage = "/images/forbidden.svg";

        include_once $vd->getRoot() . '/php/view/master.php';
        exit();
    }

    /**
     * \brief Write a 404 error page.
     */
    public static function write404($pagename) {
        header('HTTP/1.0 404 Not Found'); /* error response */
        /* error page components */
        $vd = new ViewDescriptor();
        $vd->setTitle("Error");
        $vd->setPage(ViewDescriptor::$error, Null);
        /* section title for error page */
        $title = "404 Resource Not Found.";
        /* error message */
        $message =  "The page "
                  . (is_null($pagename) ? "" : "\"")  /* quotes if needed */
                  . $pagename
                  . (is_null($pagename) ? "" : "\" ")  /* close quotes, space */
                  . "is currently unavailable. Check the URL.";
        $errorImage = "/images/broken.svg";

        include_once $vd->getRoot() . '/php/view/master.php';
        exit();
    }

    /**
     * \brief Write a 500 error page.
     * @param errorMessage Message as content of the page.
     */
    public static function write500($errorMessage, $errorImage) {
        header('HTTP/1.0 500 Internal Server Error'); /* error response */
        /* error page components */
        $vd = new ViewDescriptor();
        $vd->setTitle("Error");
        $vd->setPage(ViewDescriptor::$error, Null);
        /* section title for error page */
        $title = "500 Internal Server Error.";
        /* error message */
        $message = $errorMessage;
        if (!isset($errorImage))
            $errorImage = "/images/broken.svg";

        include_once $vd->getRoot() . '/php/view/master.php';
        exit();
    }

    /*!
     * \brief Write the login page.
     * @param prevUsername Username in a previous wrong request.
     * @param wrongUsername Message for the username error (Null for no error).
     * @param wrongPassword Message for the password error (Null for no error).
     */
    public static function login($prevUsername, $wrongUsername, $wrongPassword){
        if (isset($_SESSION[self::USER])) { /* user already logged in */
            /* protocol */
            if (isset($_SERVER['HTTPS']) && strtoupper($_SERVER['HTTPS'])=='ON')
                $protocol = "https";
            else
                $protocol = "http";
            /* hostname */
            $host  = $_SERVER['HTTP_HOST'];
            header("Location: $protocol://$host/home");
        }

        $vd = new ViewDescriptor();
        $vd->setTitle("Login");
        $vd->setPage(ViewDescriptor::$login, Null);

        include_once $vd->getRoot() . '/php/view/master.php';
        exit();
    }

    public static function logout() {
        if (!isset($_SESSION[self::USER]) || $_SESSION[self::USER] == Null) {
            self::write403("logout");
            exit();
        }

        /* destroy session array and cookie */
        $_SESSION = array();
        if (session_id() != '' || isset($_COOKIE[session_name()])) {
            /* expire session cookie */
            setcookie(session_name(), '', time() - 3000000, '/');
        }
        session_destroy();

        /* show logout page */
        $vd = new ViewDescriptor();
        $vd->setTitle("Logout");
        $vd->setPage(ViewDescriptor::$logout, Null);

        include_once $vd->getRoot() . '/php/view/master.php';

        /* redirect to home after 5 seconds */
        /* hostname */
        $host  = $_SERVER['HTTP_HOST'];
        /* protocol */
        if (isset($_SERVER['HTTPS']) && strtoupper($_SERVER['HTTPS']) == 'ON')
            $protocol = "https";
        else
            $protocol = "http";
        header("refresh:5; url=$protocol://$host/home");
    }

    /* TODO */
    public static function home() {
        $vd = new ViewDescriptor();
        $vd->setTitle("Home");

        if (isset($_SESSION[self::USER]) && $_SESSION[self::USER] != Null)
            $role = $_SESSION[self::USER]->getRole();
        else
            $role = Null;

        $vd->setPage(ViewDescriptor::$home, $role);

        include_once $vd->getRoot() . '/php/view/master.php';
        exit();
    }

    /* TODO */
    public static function about() {
        $vd = new ViewDescriptor();
        $vd->setTitle("About");
        if (isset($_SESSION[self::USER]) && $_SESSION[self::USER] != Null)
            $role = $_SESSION[self::USER]->getRole();
        else
            $role = Null;

        $vd->setPage(ViewDescriptor::$about, $role);

        include_once $vd->getRoot() . '/php/view/master.php';
        exit();
    }

    public static function registration(&$req) {
        $ajaxAnswer = Null;
        /* request for registration data validation */
        if (isset($req[self::CMD])) {
            /* command selected */
            switch ($req[self::CMD]) {
                case "regValidation":
                    $ajaxAnswer = True;
                    include $_SERVER['DOCUMENT_ROOT']
                             . '/php/control/register.php';
                    break;

                default: /* unrecognized command */
                    self::write404($req[self::PAGE]);
                    break;
            }
        }
        /* request for registration page, available to non logged users only */
        if (isset($_SESSION[self::USER])) { /* user already logged in */
            /* protocol */
            if (isset($_SERVER['HTTPS']) && strtoupper($_SERVER['HTTPS'])=='ON')
                $protocol = "https";
            else
                $protocol = "http";
            /* hostname */
            $host  = $_SERVER['HTTP_HOST'];
            header("Location: $protocol://$host/home");
        } else {
           $vd = new ViewDescriptor();
           $vd->setTitle("Registration");
           $vd->setPage(ViewDescriptor::$registration, Null);

           include_once $vd->getRoot() . '/php/view/master.php';
           exit();
       }
   }

   public static function reset(&$req) {
       /* protocol */
       if (isset($_SERVER['HTTPS']) && strtoupper($_SERVER['HTTPS'])=='ON')
           $protocol = "https";
       else
           $protocol = "http";
       /* hostname */
       $host  = $_SERVER['HTTP_HOST'];

       /* reset from link with token */
       if (isset($req[self::CMD])) {
           if ($req[self::CMD] == 'reset' && isset($req['token'])) {
               $id = 0;
               $email = "";
               $matches = 0;
               $token = $req['token'];

               /* search the reset token in the database */
               $mysqli = Database::getInstance()->connect();
               Database::checkConnection($mysqli); /* check db connection */
               $stmt = $mysqli->stmt_init();
               $stmt->prepare("SELECT Id, Email FROM Users " .
                        "WHERE ResetToken = ?");
               $stmt->bind_param("s", $token);
               $stmt->execute();
               Database::checkStmt($stmt, $mysqli); /* error handler */
               $stmt->bind_result($id, $email);
               while ($stmt->fetch()) {
                   $matches++;
               }
               $stmt->close();
               $mysqli->close(); /* close db connection */

               /* no user found */
               if ($matches == 0) {
                   self::write400();
                   exit();
               }
               /* too many users found */
               if ($matches > 1) {
                   Database::dbCorruption();
                   exit();
               }

               /* generate a new random password */
               $tempPassword = md5(mt_rand(1000000000, 9999999999));
               $passwordHash = password_hash($tempPassword, PASSWORD_DEFAULT);

               $headers  = 'From: noreply@ammproject.com' . "\r\n" .
                           'MIME-Version: 1.0' . "\r\n" .
                           'Content-type: text/html; charset=iso-8859-1' .
                           "\r\n" .
                           'X-Mailer: PHP/' . phpversion();
               $subject = 'Password Reset';
               $message =
                       "You have requested a password reset for your account. " .
                       "Your temporary password is: $tempPassword<br />" .
                       "You should change your password as soon as possible.";

               /* send the email */
               $sent = mail($email, $subject, $message, $headers);

               /* mail error */
               if (!$sent) {
                   $vd = new ViewDescriptor();
                   $vd->setPage(ViewDescriptor::$error, Null);
                   /* section title for error page */
                   $vd->setTitle("Password Reset");

                   $errorImage = "/images/mail_error.svg";
                   $title = "Mail server unavaible";
                   $message = "The local mail server is unavailable, so " .
                              "the password cannot be reset via e-mail. " .
                              "Try returning to the " .
                              "<a href=\"/home\">homepage</a> or to the " .
                              "<a href=\"/login\">login page</a>.";

                   include_once $vd->getRoot() . '/php/view/master.php';
                   exit();
               }

               /* remove token from the database and change password */
               $mysqli = Database::getInstance()->connect();
               Database::checkConnection($mysqli); /* check db connection */
               $mysqli->query("UPDATE Users SET ResetToken = NULL, " .
                        "Password_hash = \"$passwordHash\" WHERE Id = $id");
               if ($mysqli->errno > 0) {
                   error_log("Query error $mysqli->errno: $mysqli->error", 0);
                   Database::dbError($mysqli);
                   exit();
               }
               $mysqli->close(); /* close db connection */

               /* show confirmation page */
               $vd = new ViewDescriptor();
               $vd->setPage(Null, Null);
               $vd->setTitle("Password Reset Complete");

               $image = "/images/mail.svg";
               $message = "A new temporary password has been sent to your " .
                          "e-mail address. You will be redirected to the " .
                          "<a href=\"/login\">login page</a> in seconds.";

               include_once $vd->getRoot() . '/php/view/master.php';

               /* redirect to the login page */
               header("refresh:5; url=$protocol://$host/login");
               exit();
           }
           else {
               self::write400();
               exit();
           }
       }

       /* send email reset request */
       else {
           if (!isset($req['username']))
               self::write400();

           $username = $req['username'];
           $token = "";
           $id = 0;
           $email = "";
           $matches = 0;
           $mysqli = Database::getInstance()->connect();
           Database::checkConnection($mysqli); /* check db connection */

           /* get user id and email for the username */
           $stmt = $mysqli->stmt_init();
           $stmt->prepare("SELECT Id, Email FROM Users WHERE Username = ?");
           $stmt->bind_param("s", $username);
           $stmt->execute();
           Database::checkStmt($stmt, $mysqli); /* error handler */
           $stmt->bind_result($id, $email);
           while ($stmt->fetch()) {
               $matches++;
           }
           $stmt->close();

           /* Create a token and ensure it is not present in the database yet.
            * The probability is around 1e-10, but it is still possible.
            */
           do {
               $token = md5(mt_rand(1000000000, 9999999999));
               $result = $mysqli->query("SELECT COUNT(ResetToken) FROM Users " .
                       "WHERE ResetToken = \"$token\"");
               if ($mysqli->errno > 0) {
                   error_log("Query error $mysqli->errno: $mysqli->error", 0);
                   Database::dbError($mysqli);
                   exit();
               }
               $row = $result->fetch_row();
           } while ($row[0] != 0);

           $mysqli->close(); /* close db connection */

           /* no user found */
           if ($matches == 0) {
               self::login($username, "Seems not a registered username", Null);
           }
           /* too many users found */
           if ($matches > 1) {
               Database::dbCorruption();
               exit();
           }

           $host  = $_SERVER['HTTP_HOST']; /* http hostname */
           /* protocol */
           if (isset($_SERVER['HTTPS']) && strtoupper($_SERVER['HTTPS']) =='ON')
               $protocol = "https";
           else
               $protocol = "http";

           $headers  = 'From: noreply@ammproject.com' . "\r\n";
           $headers .= 'MIME-Version: 1.0' . "\r\n";
           $headers .= 'Content-type: text/html; charset=iso-8859-1' . "\r\n";
           $headers .= 'X-Mailer: PHP/' . phpversion();
           $subject = 'Password Reset';
           $message =
                   "You have requested a password reset for your account. " .
                   "Open the following link to reset the password, or ignore " .
                   "this mail if you have not requested this.<br />" .
                   "Reset link:<br />" .
                   "<a href=\"$protocol://$host/reset?cmd=reset&token=$token\">" .
                   "   href=$protocol://$host/reset?cmd=reset&token=$token" .
                   "</a>";

           /* send the email */
           $sent = mail($email, $subject, $message, $headers);

           if (!$sent) {
               $vd = new ViewDescriptor();
               $vd->setPage(ViewDescriptor::$error, Null);
               /* section title for error page */
               $vd->setTitle("Password Reset");

               $errorImage = "/images/mail_error.svg";
               $title = "Mail server unavaible";
               $message = "The local mail server is unavailable, so the password " .
                          "cannot be reset via e-mail. Try return to the " .
                          "<a href=\"/home\">homepage</a> or to the " .
                          "<a href=\"/login\">login page</a>.";

               include_once $vd->getRoot() . '/php/view/master.php';
               exit();
           }

           /* mail sent succesfully: write reset token in the database */
           $mysqli = Database::getInstance()->connect(); /* open db connection */
           Database::checkConnection($mysqli); /* check db connection */
           $query = "UPDATE Users SET ResetToken = \"$token\" " .
                    "WHERE Id = $id";
           $result = $mysqli->query($query);
           if ($mysqli->errno > 0) {
               error_log("Query error $mysqli->errno: $mysqli->error", 0);
               Database::dbError($mysqli);
               exit();
           }
           $mysqli->close(); /* close db connection */

           /* confirmation page */
           $vd = new ViewDescriptor();
           $vd->setPage(Null, Null);
           $vd->setTitle("Password Reset");

           $image = "/images/mail.svg";
           $message = "A reset e-mail has been sent to the user address. " .
                      "You will be redirected to the homepage in seconds.";

           include_once $vd->getRoot() . '/php/view/master.php';

           /* redirect to homepage */
           header("refresh:5; url=$protocol://$host/home");
           exit();
       }
   }

   public static function settings() {
       $ajaxAnswer = Null;
       /* request for registration data validation */
       if (isset($req[self::CMD])) {
           /* command selected */
           switch ($req[self::CMD]) {
               case "regValidation":
                   $ajaxAnswer = True;
                   include $_SERVER['DOCUMENT_ROOT']
                            . '/php/control/settingsChange.php';
                   break;

               default: /* unrecognized command */
                   self::write404($req[self::PAGE]);
                   break;
           }
      }
      /* confirmation page */
      $vd = new ViewDescriptor();
      $vd->setPage('settings', $_SESSION[self::USER]->getRole());
      $vd->setTitle("Settings");

      include_once $vd->getRoot() . '/php/view/master.php';
   }
}

?>