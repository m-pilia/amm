<?php
/*!
 * \file index.php
 * @author Martino Pilia <martino.pilia@gmail.com>
 * @date 2015-06-29
 *
 * This is the entry point for the application. The request array is dispatched
 * to the controller, and all the global exceptions are handled here.
 */
include_once __DIR__ . '/php/control/Controller.php';
include_once __DIR__ . '/php/model/Database.php';

ob_start(); /* start of the output buffer */

try {
    /* invoke the controller to manage page request */
    Controller::dispatch($_REQUEST);
}

/* error in the connection with the database */
catch (DbConnectionException $e) {
    ob_end_clean(); /* delete previous output buffer (warnings etc.) */

    error_log("Database connection error " .
        "(errno: " . $e->getErrno() . "; " .
        "message: " . $e->getError() . ")",
        0);

    $errorMessage = "There was a problem in the connection with the "
            . "database. Try to return to the "
            . "<a href=\"javascript:history.back()\">previous page</a>.";
    $errorImage = "images/database_error.svg";

    Controller::write500($errorMessage, $errorImage);
    exit();
}

/* the data inside the database seems corrupted */
catch (DbCorruptionException $e) {
    ob_end_clean(); /* delete previous output buffer (warnings etc.) */

    error_log($e->getMessage(), 0);

    $errorMessage = "There is something incoherent in the database "
            . "content. Try to return to the "
            . "<a href=\"javascript:history.back()\">previous page</a>.";
    $errorImage = "images/database_corruption.svg";

    Controller::write500($errorMessage, $errorImage);
    exit();
}

/* query or prepared statement failure */
catch (DbQueryException $e) {
    ob_end_clean(); /* delete previous output buffer (warnings etc.) */

    error_log($e->getMessage() . " (" .
              "errno: " . $e->getErrno() . "; " .
              "message: " . $e->getError() . ")",
              0);

    $errorMessage = "There was a problem retrieving data from the "
          . "database. Try to return to the "
          . "<a href=\"javascript:history.back()\">previous page</a>.";
    $errorImage = "images/database_error.svg";

    Controller::write500($errorMessage, $errorImage);
    exit();
}

/* unknown exception */
catch (Exception $e) {
    ob_end_clean(); /* delete previous output buffer (warnings etc.) */

    error_log($e->getMessage(), 0);

    $errorMessage = "There was a problem inside the server. "
          . "Try to return to the "
          . "<a href=\"javascript:history.back()\">previous page</a>.";
    $errorImage = "images/broken.svg";

    Controller::write500($errorMessage, $errorImage);
    exit();
}

?>
