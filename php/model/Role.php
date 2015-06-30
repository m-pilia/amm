<?php
include_once __DIR__ . "/Enum.php";

/**
 * \brief Enumeration class for user roles.
 *
 * @author Martino Pilia <martino.pilia@gmail.com>
 * @date 2015-04-29
 */
abstract class Role extends Enum {
    const ADMIN = 'Admin';
    const USER = 'User';
}
?>
