<?php
$root = $_SERVER['DOCUMENT_ROOT'];
include_once $root . '/php/view/ViewDescriptor.php';
include_once $root . '/php/control/FrontController.php';

/*!
 * \brief Generic controller.
 * @author Martino Pilia <martino.pilia@gmail.com>
 * @date 2015-06-09
 */
class BaseController {

    public function __construct() {

    }

    public function handleInput(&$request) {
        $vd = new ViewDescriptor();

        if (isset($req[FrontController::CMD])) {
            switch ($req[FrontController::CMD]) {
                default:
                    /* TODO */
                    break;
            }
        }

    }
}
?>
