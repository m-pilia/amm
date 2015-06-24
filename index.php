<?php
include_once $_SERVER['DOCUMENT_ROOT'] . '/php/control/FrontController.php';

/* read page request and invoke the right controller to manage it */
FrontController::dispatch($_REQUEST);

?>
