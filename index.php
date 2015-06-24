<?php
include_once __DIR__ . '/php/control/FrontController.php';

/* read page request and invoke the right controller to manage it */
FrontController::dispatch($_REQUEST);

?>
