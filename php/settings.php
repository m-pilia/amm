<?php
/* timezone */
date_default_timezone_set('Europe/Rome');

/* security options */
$options = array('cost' => 10); /* Hashing options */

/* registration options */
$minPasswordLength = 8; /* minimum chars in a valid password */
$maxImageSize = 200; /* maximum avatar image size in kB */
$uploadDir = "uploads/"; /* folder for uploaded files */
$defaultAvatar = "images/default_avatar.svg"; /* default image */
$maxNameLen = 128; /* maximum first and last name length */
$maxUsernameLen = 128; /* maximum username length */
$maxEmailLen = 320; /* maximum email length */

/* maximum name length for resources
 * NOTE: it's limited to 255 because of MySQL size limit for unique fields */
$maxResourceNameLen = 255;

/* behaviour settings */
$beginHour = 7; /* first bookable hour of the day */
$endHour = 22; /* last bookable hour of the day */
$notesLen = 2048; /* maximum length for event's notes */

/* display options */
$trimLen = 24; /* number of characters for trim in the tables */

?>
