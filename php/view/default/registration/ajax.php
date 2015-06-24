<?php
$json = array(
        /* nonzero if the whole form is valid, zero otherwise */
        'valid' => $allValid,
        /* error messages */
        'username' => $wrongUsername,
        'password' => $wrongPassword ,
        'password-rep' => $wrongRepeatedPassword,
        'email' => $wrongEmail,
        'first' => $wrongFirst,
        'last' => $wrongLast);
echo json_encode($json);
?>
