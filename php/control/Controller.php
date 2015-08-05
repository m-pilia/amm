<?php

require_once __DIR__ . "/../model/User.php";
require_once __DIR__ . "/../model/Resource.php";
require_once __DIR__ . "/../model/Event.php";
require_once __DIR__ . "/../model/EventSet.php";
require_once __DIR__ . "/../view/ViewDescriptor.php";
require_once __DIR__ . "/../settings.php";

/**
 * \brief The application entry point, which sorts requests.
 * @author Martino Pilia <martino.pilia@gmail.com>
 * @date 2015-04-29
 */
class Controller {

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
     * \brief Get the server's hostname.
     * @return string The value of `$_SERVER['HTTP_HOST']`.
     */
    public static function getHost() {
        return $_SERVER['HTTP_HOST'];
    }

    /**
     * \brief Get the protocolo currently in use for the connection.
     * @return string "http" or "https".
     */
    public static function getProtocol() {
        if (isset($_SERVER['HTTPS']) && strtoupper($_SERVER['HTTPS'])=='ON')
            return "https";
        else
            return "http";
    }

    /**
     * \brief the folder containing the script currently in execution.
     * @return string The folder containing the filename returned by
     *                `$_SERVER['PHP_SELF']`.
     */
    public static function getFolder() {
        return rtrim(dirname($_SERVER['PHP_SELF']), "/");
    }

    /**
     * \brief Manage a page request.
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
                    self::write403($req[self::PAGE]);
                    break;

                case "404": /* idem */
                    self::write404($req[self::PAGE]);
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
                    self::login($req, Null, Null, Null);
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
                        self::write403($req[self::PAGE]);
                    self::settings($req);
                    break;

                case "calendar":
                    if (!isset($_SESSION[self::USER]))
                        self::write403($req[self::PAGE]);
                    self::calendar($req);
                    break;

                case "createEvent":
                    if (!isset($_SESSION[self::USER]))
                        self::write403($req[self::PAGE]);
                    self::createEvent($req);
                    break;

                case "displayEvent":
                    if (!isset($_SESSION[self::USER]))
                        self::write403($req[self::PAGE]);
                    self::displayEvent($req);
                    break;

                case "editEvent":
                    if (!isset($_SESSION[self::USER]))
                        self::write403($req[self::PAGE]);
                    self::editEvent($req);
                    break;

                case "resourceManager":
                    if (!isset($_SESSION[self::USER])
                            || $_SESSION[self::USER]->getRole() != "Admin")
                        self::write403($req[self::PAGE]);
                    self::resourceManager($req);
                    break;

                case "userManager":
                    if (!isset($_SESSION[self::USER])
                            || $_SESSION[self::USER]->getRole() != "Admin")
                        self::write403($req[self::PAGE]);
                    self::userManager($req);
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
        if (!session_id())
            session_start();
        $role = Null;
        if (isset($_SESSION['user']))
            $role = $_SESSION['user']->getRole();

        header('HTTP/1.0 400 Bad Request'); /* error response */
        /* error page components */
        $vd = new ViewDescriptor();
        $vd->setTitle("Error");
        $vd->setPage(ViewDescriptor::$error, $role);
        /* section title for error page */
        $title = "400 Bad Request.";
        /* error message */
        $message =  "The requested action seems invalid. Try returning to the "
                  . "<a href=\"javascript:history.back()\">previous page</a>.";
        $errorImage = "images/broken.svg";

        include_once __DIR__ . '/../view/master.php';
        exit();
    }

    /**
     * \brief Write a 403 error page.
     */
    public static function write403($pagename) {
        if (!session_id())
            session_start();
        $role = Null;
        if (isset($_SESSION['user']))
            $role = $_SESSION['user']->getRole();

        header('HTTP/1.0 403 Forbidden'); /* error response */
        /* error page components */
        $vd = new ViewDescriptor();
        $vd->setTitle("Error");
        $vd->setPage(ViewDescriptor::$error, $role);
        /* section title for error page */
        $title = "403 Forbidden.";
        /* error message */
        $message =  "You don't have the needed permission to access the page "
                  . (is_null($pagename) ? "" : "\"")  /* quotes if needed */
                  . $pagename
                  . (is_null($pagename) ? "" : "\" ")  /* close quotes, space */
                  . ". Try "
                  . "<a href=\"javascript:history.back()\">going back</a> "
                  . "or return to the <a href=\"home\">homepage</a>.";
        $errorImage = "images/forbidden.svg";

        include_once __DIR__ . '/../view/master.php';
        exit();
    }

    /**
     * \brief Write a 404 error page.
     */
    public static function write404($pagename) {
        if (!session_id())
            session_start();
        $role = Null;
        if (isset($_SESSION['user']))
            $role = $_SESSION['user']->getRole();

        header('HTTP/1.0 404 Not Found'); /* error response */
        /* error page components */
        $vd = new ViewDescriptor();
        $vd->setTitle("Error");
        $vd->setPage(ViewDescriptor::$error, $role);
        /* section title for error page */
        $title = "404 Resource Not Found.";
        /* error message */
        $message =  "The page "
                  . (is_null($pagename) ? "" : "\"")  /* quotes if needed */
                  . $pagename
                  . (is_null($pagename) ? "" : "\" ")  /* close quotes, space */
                  . "is currently unavailable. Check the URL.";
        $errorImage = "images/broken.svg";

        include_once __DIR__ . '/../view/master.php';
        exit();
    }

    /**
     * \brief Write a 500 error page.
     * @param errorMessage Message as content of the page.
     */
    public static function write500($errorMessage, $errorImage) {
        if (!session_id())
            session_start();
        $role = Null;
        if (isset($_SESSION['user']))
            $role = $_SESSION['user']->getRole();

        header('HTTP/1.0 500 Internal Server Error'); /* error response */
        /* error page components */
        $vd = new ViewDescriptor();
        $vd->setTitle("Error");
        $vd->setPage(ViewDescriptor::$error, $role);
        /* section title for error page */
        $title = "500 Internal Server Error.";
        /* error message */
        $message = $errorMessage;
        if (!isset($errorImage))
            $errorImage = "/images/broken.svg";

        include_once __DIR__ . '/../view/master.php';
        exit();
    }

    /**
     * \brief Write the login page.
     * @param array  $req           The $_REQUEST array.
     * @param string $prevUsername  Username in a previous wrong request.
     * @param string $wrongUsername Message for the username error (Null for
     *                              no error).
     * @param string $wrongPassword Message for the password error (Null for
     *                              no error).
     */
    private static function login($req, $prevUsername, $wrongUsername,
                $wrongPassword) {

        require __DIR__ . "/../settings.php";

        $host = self::getHost();
        $folder = self::getFolder();
        $protocol = self::getProtocol();

        /* check if the user is already logged in */
        if (isset($_SESSION[self::USER])) {
            /* redirect to the homepage */
            header("Location: $protocol://$host$folder/home");
        }

        /* manage commands */
        if (isset($req) && isset($req[self::CMD])) {

            /* auth request */
            if ($req[self::CMD] == "auth") {
                /* error messages for missing/invalid username/password */
                $userProblem = Null;
                $passProblem = Null;

                /* input credentials */
                $username = isset($req['username']) ? $req['username'] : '';
                $password = isset($req['password']) ? $req['password'] : '';

                /* check for the requested user */
                list($userProblem, $passProblem, $id) =
                        User::checkUser($username, $password);

                /* if at least one credential is missing/invalid, return to the
                 * login page with an error indication */
                if ($userProblem || $passProblem) {
                    Controller::login(Null, $username, $userProblem,
                            $passProblem);
                    exit();
                }

                /* Otherwise, the login data is valid: create a user object
                 * and redirect him/her to the home.
                 * NOTE: the method newUserById returns an object of the
                 * right class according to the user's role. */
                try {
                    $user = User::newUserById($id);
                } catch (InvalidUserException $e) {
                    throw new DbCorruptionException(
                            "User with id $id has an invalid role");
                }
                $_SESSION[Controller::USER] = $user;

                /* redirect to homepage */
                header("Location: $protocol://$host$folder/home");
                exit();
            }

            /* invalid command */
            else {
                self::write400();
                break;
            }
        }

        /* no commands: show login page */
        $vd = new ViewDescriptor();
        $vd->setTitle("Login");
        $vd->setPage(ViewDescriptor::$login, Null);

        include_once __DIR__ . '/../view/master.php';
        exit();
    }

    /**
     * \brief Write the logout page.
     * @param array $req The $_REQUEST array.
     */
    private static function logout() {
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

        include_once __DIR__ . '/../view/master.php';

        /* redirect to home after 3 seconds */
        $host = self::getHost();
        $protocol = self::getProtocol();
        $folder = self::getFolder();
        header("refresh:3; url=$protocol://$host$folder/home");
    }

    /**
     * \brief Write the homepage.
     * @param array $req The $_REQUEST array.
     */
    private static function home() {
        if (isset($_SESSION[self::USER]) && $_SESSION[self::USER] != Null)
            $role = $_SESSION[self::USER]->getRole();
        else
            $role = Null;

        $vd = new ViewDescriptor();
        $vd->setTitle("Resource Booking Application - Home");
        $vd->setPage(ViewDescriptor::$home, $role);

        include_once __DIR__ . '/../view/master.php';
        exit();
    }

    /**
     * \brief Write the about page.
     * @param array $req The $_REQUEST array.
     */
    private static function about() {
        $vd = new ViewDescriptor();
        $vd->setTitle("About");
        if (isset($_SESSION[self::USER]) && $_SESSION[self::USER] != Null)
            $role = $_SESSION[self::USER]->getRole();
        else
            $role = Null;

        $vd->setPage(ViewDescriptor::$about, $role);

        include_once __DIR__ . '/../view/master.php';
        exit();
    }

    /**
     * \brief Manage registration.
     * @param array $req The $_REQUEST array.
     */
    private static function registration(&$req) {
        require __DIR__ . "/../settings.php";
        $protocol = self::getProtocol();
        $host  = self::getHost();
        $folder = self::getFolder();

        /* command for registration submission or data validation */
        if (isset($req[self::CMD])) {

            /* get data from the request */
            $username = isset($_REQUEST['username']) ?
                    $_REQUEST['username'] : '';
            $password = isset($_REQUEST['password']) ?
                    $_REQUEST['password'] : '';
            $repeatedPassword = isset($_REQUEST['password-rep']) ?
                    $_REQUEST['password-rep'] : '';
            $email = isset($_REQUEST['email']) ? $_REQUEST['email'] : '';
            $first = isset($_REQUEST['first']) ? $_REQUEST['first'] : '';
            $last = isset($_REQUEST['last']) ? $_REQUEST['last'] : '';
            $avatar = isset($_REQUEST['avatar']) ? $_REQUEST['avatar'] : '';

            /* validate data */
            $result = User::validateUserData(
                    $username,
                    $password,
                    $repeatedPassword,
                    $email,
                    $first,
                    $last,
                    $avatar);

            /* variables with validated data and eventual error messages */
            extract($result);

            switch ($req[self::CMD]) {
                case "validation":
                    /* response to an AJAX request for validation */
                    /* accessible to logged users for the AJAX validation
                     * in the settings page */
                    include __DIR__ . '/../view/default/registration/ajax.php';
                    exit();

                case "submit":
                    /* response to a registration request */

                    /* user already logged in: ignore and redirect to home */
                    if (isset($_SESSION[self::USER]))
                        header("Location: $protocol://$host$folder/home");

                    if (!$allValid) {
                        /* registration failed: show the registration page,
                         * with errors highlighted */
                        $vd = new ViewDescriptor();
                        $vd->setTitle("Registration");
                        $vd->setPage(ViewDescriptor::$registration, Null);
                        include_once __DIR__ . '/../view/master.php';
                        exit();
                    }

                    /* otherwise, data is valid */
                    /* add the user to the database */
                    $user = User::registerUser($username, $password, $email,
                            $first, $last, $avatar);

                    /* open session */
                    if (!session_id())
                        session_start();

                    /* create user object in the session array */
                    $_SESSION['user'] = $user;

                    /* show registration confirmation page */
                    $vd = new ViewDescriptor();
                    $vd->setTitle("Registration success");
                    $vd->setPage('generic_page', $_SESSION['user']->getRole());
                    $title = "Registration success";
                    $message = "Congratulations! You have completed the " .
                               "registration successfully, and you will be " .
                               "redirected to the " .
                               "<a href=\"home\">homepage</a> in seconds.";
                    include_once __DIR__ . '/../view/master.php';

                    /* redirect to the homepage after 3 seconds */
                    header("refresh:3; url=$protocol://$host$folder/home");
                    exit();

                default: /* unrecognized command */
                    self::write400();
                    break;
            }
        }

        /* user already logged in: ignore and redirect to home */
        if (isset($_SESSION[self::USER]))
            header("Location: $protocol://$host$folder/home");

        /* no command: show registration page */
        $vd = new ViewDescriptor();
        $vd->setTitle("Registration");
        $vd->setPage(ViewDescriptor::$registration, Null);

        /* precompile  username field */
        if (isset($req['username']))
            $username = $req['username'];

        include_once __DIR__ . '/../view/master.php';
        exit();
    }

    /**
     * \brief Manage the password reset.
     * @param array $req The $_REQUEST array.
     *
     * This method manages the password reset procedure. If the user asks
     * for a password reset in the web page, a reset token is set for him
     * in the database and a confirmation email is sent to him. When the
     * user opens the link in the confirmation mail, his password is
     * changed with a random one, and the new password is sent to the
     * user with another email.
     */
    private static function reset(&$req) {
        $protocol = self::getProtocol();
        $host = self::getHost();
        $folder = self::getFolder();

        /* reset from link with token */
        if (isset($req[self::CMD])) {
            if ($req[self::CMD] == 'reset' && isset($req['token'])) {

                /* reset token */
                $token = $req['token'];

                /* search token owner */
                $values = User::getResetTokenOwner($token);

                if (!$values) {
                    /* no owner found */
                    self::write400();
                    exit();
                }
                else {
                    list($id, $email) = $values;
                }

                /* generate a new random password */
                $tempPassword = md5(mt_rand(1000000000, 9999999999));

                /* email content */
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

                /* send the email with the new password */
                $sent = mail($email, $subject, $message, $headers);

                /* mail error */
                if (!$sent) {
                    $vd = new ViewDescriptor();
                    $vd->setPage(ViewDescriptor::$error, Null);
                    /* section title for error page */
                    $vd->setTitle("Password Reset");

                    $errorImage = "images/mail_error.svg";
                    $title = "Mail server unavailable";
                    $message = "The local mail server is unavailable, so " .
                               "the password cannot be reset via e-mail. " .
                               "Try returning to the " .
                               "<a href=\"home\">homepage</a> or to the " .
                               "<a href=\"login\">login page</a>.";

                    include_once __DIR__ . '/../view/master.php';
                    exit();
                }

                /* remove the token from the database and change the password */
                User::passwordChange($id, $tempPassword);

                /* show confirmation page */
                $vd = new ViewDescriptor();
                $vd->setPage(Null, Null);
                $vd->setTitle("Password Reset Complete");

                $image = "images/mail.svg";
                $message = "A new temporary password has been sent to your " .
                          "e-mail address. You will be redirected to the " .
                          "<a href=\"login\">login page</a> in seconds.";

                include_once __DIR__ . '/../view/master.php';

                /* redirect to the login page */
                header("refresh:3; url=$protocol://$host$folder/login");
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

           /* get user */
           $values = User::generateTokenForUser($username);

           if (!$values) {
               /* no user found */
               self::login(Null,
                           $username,
                           "Seems not a registered username",
                           Null);
           }
           else {
               list($id, $email, $token) = $values;
           }

           /* email content */
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
                   "<a href=\"$protocol://$host$folder/" .
                   "reset?cmd=reset&token=$token\">" .
                   "   href=$protocol://$host$folder/" .
                   "reset?cmd=reset&token=$token" .
                   "</a>";

           /* send the email */
           $sent = mail($email, $subject, $message, $headers);

           if (!$sent) {
               $vd = new ViewDescriptor();
               $vd->setPage(ViewDescriptor::$error, Null);
               /* section title for error page */
               $vd->setTitle("Password Reset");

               $errorImage = "/images/mail_error.svg";
               $title = "Mail server unavailable";
               $message = "The local mail server is unavailable, so the " .
                          "password cannot be reset via e-mail. " .
                          "Try return to the " .
                          "<a href=\"home\">homepage</a> or to the " .
                          "<a href=\"login\">login page</a>.";

               include_once __DIR__ . '/../view/master.php';
               exit();
            }

            /* mail sent succesfully: write reset token in the database */
            User::setResetToken($id, $token);

            /* confirmation page */
            $vd = new ViewDescriptor();
            $vd->setPage(Null, Null);
            $vd->setTitle("Password Reset");

            $image = "images/mail.svg";
            $message = "A reset e-mail has been sent to the user address. " .
                      "You will be redirected to the " .
                      "<a href=\"home\">homepage</a> in seconds.";

            include_once __DIR__ . '/../view/master.php';

            /* redirect to the homepage */
            header("refresh:3; url=$protocol://$host$folder/home");
            exit();
        }
    }

    /**
     * \brief Show the setings page and manage settings changes.
     * @param array $req The $_REQUEST array.
     */
    private static function settings(&$req) {
        require __DIR__ . "/../settings.php";

        /* request for data submission */
        if (isset($req[self::CMD])) {
            /* command selected */
            switch ($req[self::CMD]) {
                case "submit":
                    $host = self::getHost();
                    $protocol = self::getProtocol();
                    $folder = self::getFolder();

                    if (!session_id())
                        session_start();

                    $password = isset($_REQUEST['password']) ?
                            $_REQUEST['password'] : '';
                    $repeatedPassword = isset($_REQUEST['password-rep']) ?
                            $_REQUEST['password-rep'] : '';

                    /* validate new password */
                    $result = User::validateUserData(
                            Null,
                            $password,
                            $repeatedPassword,
                            Null,
                            Null,
                            Null,
                            Null);

                    /* create variables for validated data or error messages */
                    extract($result);

                    if ($wrongPassword || $wrongRepeatedPassword) {
                        /* something is invalid: redirect to the settings page,
                        * with errors highlighted */
                        $vd = new ViewDescriptor();
                        $vd->setTitle("Settings");
                        $vd->setPage(ViewDescriptor::$settings,
                                $_SESSION['user']->getRole());
                        include_once __DIR__ . '/../view/master.php';
                        exit();
                    }

                    /* change password in the database */
                    User::passwordChange($_SESSION['user']->getId(), $password);

                    /* return to settings, with a confirmation message */
                    $vd = new ViewDescriptor();
                    $vd->setTitle("Settings");
                    $vd->setPage(ViewDescriptor::$settings,
                            $_SESSION['user']->getRole());
                    $confirmationMessage =
                            "<div class=\"confirm-message\">" .
                                "Password successfully changed." .
                            "</div>";
                    include_once __DIR__ . '/../view/master.php';
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

        include_once __DIR__ . '/../view/master.php';
    }

    /**
     * \brief Show the calendar page.
     * @param array $req The $_REQUEST array.
     */
    private static function calendar(&$req) {

        /* eventual alert message */
        $alertMessage = Null;

        /* variables for date fields */
        $day = Null;
        $month = Null;
        $year = Null;

        /* variables for the event fields */
        $owner = Null;
        $start = Null;
        $end = Null;
        $resource = Null;
        $type = Null;
        $notes = Null;

        /* set date fields */
        if (isset($req['day']) && is_numeric($req['day']))
            $day = $req['day'];
        if (isset($req['month']) && is_numeric($req['month']))
            $month = $req['month'];
        if (isset($req['year']) && is_numeric($req['year']))
            $year = $req['year'];

        /* date validation */
        if (!checkdate($month, $day, $year)) {
            $day = date("d");
            $month = date("m");
            $year = date("Y");
        }

        /* resources and events */
        $resources = Resource::getResources();
        $events = Event::getEventsByDate($day, $month, $year);

        /* show calendar page */
        $vd = new ViewDescriptor();
        $vd->setPage('calendar', $_SESSION[self::USER]->getRole());
        $vd->setTitle("Calendar");
        include_once __DIR__ . '/../view/master.php';
    }

    /**
     * \brief Create an event.
     * @param  array $req $_REQUEST array.
     */
    private static function createEvent(&$req) {
        /* resources */
        $resources = Resource::getResources();

        /* variables for precompiled fields */
        $date = date("d/m/Y");
        $start = Null;
        $end = Null;
        $resource = Null;

        /* NOTE: only the value for the date is validated here, other
         * values are just ignored by comparations in the page content
         * code, if invalid. These are just placeholders, and the actual
         * values for the event creation will be validated after the
         * form's submission. */
        if (isset($req['date'])) {
            /* extract date fields (day/month/year) */
            $fields = explode('/', $req['date']);
            /* validate */
            if (sizeof($fields) == 3 &&
                    checkdate($fields[1], $fields[0], $fields[2]))
                $date = $req['date'];
        }
        if (isset($req['start']))
            $start = $req['start'];
        if (isset($req['end']))
            $end = $req['end'];
        if (isset($req['resource']))
            $resource = $req['resource'];

        /* command */
        if (isset($req[self::CMD])) {
            /* command selected */
            switch ($req[self::CMD]) {
                case "save":
                    self::handleEventSaving($req, Null);
                    break;

                default: /* unrecognized command */
                    self::write404($req[self::PAGE]);
                    break;
            }
        }
        /* no command, show the event creation page */
        else {
            /* display the page for event creation */
            $vd = new ViewDescriptor();
            $vd->setPage('create_event', $_SESSION[self::USER]->getRole());
            $vd->setTitle("Create event");
            include_once __DIR__ . '/../view/master.php';
        }
    }

    /**
     * \brief Edit an existing event.
     * @param  array $req $_REQUEST array.
     */
    private static function editEvent(&$req) {

        /* the id is required to edit an event */
        if (!isset($req['id'])
                || !filter_var($req['id'], FILTER_VALIDATE_INT)) {
            self::write400();
        }

        $id = $req['id'];
        $user = $_SESSION['user'];

        /* handle commands */
        if (isset($req[self::CMD])) {
            if ($req[self::CMD] == "save") {
                /* save the event */
                self::handleEventSaving($req, $id);
            }
            else {
                /* unrecognized command */
                self::write400($req[self::PAGE]);
            }
        }

        /* search event in the database */
        $event = Event::getEventById($id);

        /* event not found */
        if (!$event) {
            $errorImage = "images/broken.svg";
            $title = "Event not found";
            $message =
                    "The requested event does not exist. Try " .
                    "<a href=\"javascript:history.back()\">going back</a> " .
                    "or return to the <a href=\"home\">homepage</a>.";
            $vd = new ViewDescriptor();
            $vd->setPage('error', $_SESSION[self::USER]->getRole());
            $vd->setTitle("Event");
            include_once __DIR__ . '/../view/master.php';
            exit();
        }

        /* the requesting user is not an admin or the event owner */
        if ($user->getRole() != "Admin"
                && $event->getOwner() != $user->getUsername()) {
            $errorImage = "images/forbidden.svg";
            $title = "You do not own this event";
            $message =
                    "You cannot edit an event you do not own. Try " .
                    "<a href=\"javascript:history.back()\">going back</a> " .
                    "or return to the <a href=\"home\">homepage</a>.";
            $vd = new ViewDescriptor();
            $vd->setPage('error', $_SESSION[self::USER]->getRole());
            $vd->setTitle("Event");
            include_once __DIR__ . '/../view/master.php';
            exit();
        }

        /* Check if the event has started yet. A started event cannot be
         * edited.
         * Date and hour are compared by lexicographic order in the
         * yyyymmdd and hhmm formats. */
        $dateString = $event->getYear()
                . sprintf('%02d', $event->getMonth())
                . sprintf('%02d', $event->getDay());
        $hourString = sprintf('%02d', floor($event->getStart() / 2))
                . ($event->getStart() % 2 == 0 ? "00" : "30");
        /* check if the date is past, or when it is equal
         * check if the start hour is past */
        if (strcmp($dateString, date("Ymd")) < 0 || /* past date */
                (strcmp($dateString, date("Ymd")) == 0 && /* equal date */
                 strcmp($hourString, date('Hi')) < 0)) { /* past hour */
            /* started event, cannot be edited */
            $errorImage = "images/time.svg";
            $title = "The event has already started";
            $message =
                    "You cannot edit a started event. Try " .
                    "<a href=\"javascript:history.back()\">going back</a> " .
                    "or return to the <a href=\"home\">homepage</a>.";
            $vd = new ViewDescriptor();
            $vd->setPage('error', $_SESSION[self::USER]->getRole());
            $vd->setTitle("Event");
            include_once __DIR__ . '/../view/master.php';
            exit();
        }

        /* set variables to fill the input fields in the edit page */
        $resources = Resource::getResources();
        $date = $event->getDay() . "/" . $event->getMonth()
                . "/" . $event->getYear();
        $resource = $event->getResource()->getId();
        $start = $event->getStart();
        $end = $event->getEnd();
        $notes = $event->getNotes();

        /* Show event edit page.
         * This actually is the same page for event creation, with only a
         * different title. */
        $vd = new ViewDescriptor();
        $vd->setPage('create_event', $_SESSION[self::USER]->getRole());
        $vd->setTitle("Edit event");
        include_once __DIR__ . '/../view/master.php';
        exit();
    }

    /**
     * \brief Validate and save an event, both new or edited.
     * @param  array  $req    Request array.
     * @param  int    $selfId Id of the existing event, if editing.
     * @note This function contains shared code for createEvent(&$req) and
     *       editEvent(&$req) methods.
     */
    private static function handleEventSaving(&$req, $selfId) {
        $protocol = self::getProtocol();
        $host = self::getHost();
        $folder = self::getFolder();

        $date = isset($req['date']) ? $req['date'] : '';
        $resource = isset($req['resource']) ? $req['resource'] : '';
        $start  = isset($req['start']) ? $req['start'] : '';
        $end = isset($req['end']) ? $req['end'] : '';
        $notes = isset($req['notes']) ? $req['notes'] : '';

        /* validate the event data */
        $result = Event::validateEventData($date, $resource, $start, $end,
            $notes, $selfId);

        /* create variables with validated data
         * and error messages */
        extract($result);

        /* some error detected */
        if (!$allValid) {
            $resources = Resource::getResources();

            /* event creation case */
            if (!$selfId) {
                /* show event creation page with error messages */
                $vd = new ViewDescriptor();
                $vd->setPage('create_event',
                        $_SESSION[self::USER]->getRole());
                $vd->setTitle("Create event");
                include_once __DIR__ . '/../view/master.php';
                exit();
            }
            /* event edit case */
            else {
                /* variables for the view */
                $date = $day . "/" . $month . "/" . $year;
                $id = $selfId;

                /* show event edit page with error messages */
                $vd = new ViewDescriptor();
                $vd->setPage('create_event',
                        $_SESSION[self::USER]->getRole());
                $vd->setTitle("Edit event");
                include_once __DIR__ . '/../view/master.php';
                exit();
            }
        }

        /* the data is valid, create an event object */
        $event = new Event(
                $selfId,
                $_SESSION['user']->getId(),
                $day,
                $month,
                $year,
                $start,
                $end,
                new Resource($resource, Null),
                $_SESSION['user']->getRole(),
                $notes);

        /* when editing a pre-existing event */
        if ($selfId) {
            /* save edited event in the database */
            $event->editEvent();

            /* address for the right calendar page */
            $address = "calendar" .
                       "?day=" . $day .
                       "&month=" . $month .
                       "&year=" . $year;

            /* redirect to the calendar page containing the event */
            header("Location: $protocol://$host$folder/$address");
            exit();
        }

        /* insert new event in the database */
        $event->insertEvent();

        /* address for the right calendar page */
        $address = "calendar" .
                   "?day=" . $day .
                   "&month=" . $month .
                   "&year=" . $year;

        /* redirect to the calendar page */
        header("Location: $protocol://$host$folder/$address");
        break;
    }

    /**
     * \brief Show the page with an event's details.
     * @param array $req The $_REQUEST array.
     */
    private static function displayEvent(&$req) {
        if (!isset($req['id'])
                || !filter_var($req['id'], FILTER_VALIDATE_INT)) {
            self::write400();
        }

        $id = $req['id'];

        /* search event in the database */
        $event = Event::getEventById($id);

        if ($event) {
            /* display the page for the event */
            $vd = new ViewDescriptor();
            $vd->setPage('display_event', $_SESSION[self::USER]->getRole());
            $vd->setTitle("Event");
            include_once __DIR__ . '/../view/master.php';
        }
        else {
            /* event not found */
            $errorImage = "images/broken.svg";
            $title = "Event not found";
            $message =
                    "The requested event does not exist. Try " .
                    "<a href=\"javascript:history.back()\">going back</a> " .
                    "or return to the <a href=\"home\">homepage</a>.";
            $vd = new ViewDescriptor();
            $vd->setPage('error', $_SESSION[self::USER]->getRole());
            $vd->setTitle("Event");
            include_once __DIR__ . '/../view/master.php';
        }
    }

    /**
     * \brief Show the resource management page.
     * @param array $req The $_REQUEST array.
     */
    private static function resourceManager(&$req) {
        /* handle commands */
        if (isset($req[self::CMD])) {

            /* Get the requests for resource adding, renaming or deletion. */
            if ($req[self::CMD] == "submit") {
                $resources = Resource::getResources();

                $toBeDeleted = array(); /* resources to be deleted */
                $toBeUpdated = array(); /* resources to be updated */
                $toBeAdded = array(); /* names of resources to be added */

                foreach ($resources as $r) {
                    $id = $r->getId();

                    /* add to deletion array if requested */
                    if (isset($req["$id-del"]))
                        $toBeDeleted[] = $r; /* note: this is an object */

                    /* add to update array if requested */
                    elseif (isset($req[$id])) {
                        $r->setName($req[$id]);
                        $toBeUpdated[] = $r; /* note: this is an object */
                    }
                }

                /* get the keys of the req array which are in the
                 * form "/new-[0-9]+/", i.e. the names of the input fields
                 * added via javascript, indicating the names of the
                 * new resources to be added */
                $reqKeys = array_keys($req);
                $newKeys = preg_grep("/new-[0-9]+/", $reqKeys);

                /* get the names for the new resources */
                foreach ($newKeys as $k)
                    /* note: this is a string */
                    $toBeAdded[] = filter_var($req[$k], FILTER_SANITIZE_STRING);

                /* validate new names and update resources in the database */
                Resource::updateResources($toBeAdded,
                        $toBeDeleted, $toBeUpdated);
            }
            else
                self::write400(); /* unrecognized command */
        }

        $resources = Resource::getResources();

        /* show resource manager page */
        $vd = new ViewDescriptor();
        $vd->setPage('resource_manager', $_SESSION[self::USER]->getRole());
        $vd->setTitle("Resource manager");
        include_once __DIR__ . '/../view/master.php';
        exit();
    }

    /**
     * \brief Show the user management page.
     * @param array $req The $_REQUEST array.
     */
    private static function userManager(&$req) {
        try {
            $users = User::getUsers();
        }
        catch (InvalidUserException $e) {
            throw new DbCorruptionException($e->getMessage());
        }

        /* handle commands */
        if (isset($req[self::CMD])) {

            /* Get the requests for user deletion or role change. */
            if ($req[self::CMD] == "submit") {
                $users = User::getUsers();

                foreach ($users as $u) {
                    $id = $u->getId();

                    /* ignore edits on the user's self account */
                    if ($id == $_SESSION['user']->getId())
                        continue;

                    /* user deletion */
                    if (isset($req["$id-del"])) {
                        $u->deleteUser();
                        continue;
                    }

                    /* role change request */
                    if (isset($req["$id-role"]))
                        $role = Role::ADMIN;
                    else
                        $role = Role::USER;

                    /* update the role for the user if it has been changed */
                    if ($role != $u->getRole())
                        $u->setRole($role);

                }
            }
            else
                self::write400(); /* unknown command */
        }

        $users = User::getUsers();

        /* show resource manager page */
        $vd = new ViewDescriptor();
        $vd->setPage('user_manager', $_SESSION[self::USER]->getRole());
        $vd->setTitle("User manager");
        include_once __DIR__ . '/../view/master.php';
        exit();
    }
}

?>
