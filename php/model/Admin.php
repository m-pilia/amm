<?php
include_once __DIR__ . '/User.php';

/**
 * \brief Class defining an administrator.
 *
 * @author Martino Pilia <martino.pilia@gmail.com>
 * @date 2015-04-29
 */
class Admin extends User {

    /**
     * \brief Create an object representing an Admin.
     * @param string $username Admin username.
     * @param string $first    Admin first name.
     * @param string $last     Admin last name.
     * @param string $email    Admin email address.
     * @param string $avatar   Admin avatar filename.
     */
    public function __construct($id, $username, $first, $last,
            $email, $avatar) {
        parent::__construct($id, $username, $first, $last, $email, $avatar);
        $this->role = Role::ADMIN;
    }
}

?>
