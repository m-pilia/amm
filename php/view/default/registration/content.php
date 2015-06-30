<?php
/* Registration form
 */

if (!isset($confirmationMessage))
    $confirmationMessage = Null;
if (!isset($username))
    $username = Null;
if (!isset($password))
    $password = Null;
if (!isset($repeatedPassword))
    $repeatedPassword = Null;
if (!isset($email))
    $email = Null;
if (!isset($first))
    $first = Null;
if (!isset($last))
    $last = Null;
if (!isset($wrongUsername))
    $wrongUsername = Null;
if (!isset($wrongPassword))
    $wrongPassword = Null;
if (!isset($wrongRepeatedPassword))
    $wrongRepeatedPassword = Null;
if (!isset($wrongEmail))
    $wrongEmail = Null;
if (!isset($wrongFirst))
    $wrongFirst = Null;
if (!isset($wrongLast))
    $wrongLast = Null;
if (!isset($wrongImage))
    $wrongImage = Null;
if (!isset($prevUsername))
    $prevUsername = Null;
?>

<script src="js/registration_form_validation.js"
    type="text/javascript">
</script>

<h2>Registration</h2>
<form   name="registration-form"
        id="registration-form"
        class="input-form"
        action="registration?cmd=submit"
        onsubmit="return ajaxValidation()"
        method="POST"
        enctype="multipart/form-data">
    <div class="form-contour">
        <!-- left column of the form -->
        <div class="form-left">
            <input id="reg-username-field" type="text"
                name="username"
                placeholder="Username"
                oninput="ajaxValidation('username')"
                autocomplete="off"
                value="<?= $username ?>"
                class="<?php if ($wrongUsername) echo "input-error"; ?>"/>
            <br />
            <?php
                $errorId = "username-error";
                $errorMessage = $wrongUsername;
                include __DIR__ . "/error.php";
            ?>

            <input id="reg-password-field" type="password"
                name="password"
                placeholder="Password"
                oninput="ajaxValidation('password')"
                onkeypress="return capsLockAlert(event);"
                autocomplete="off"
                class="<?php if ($wrongPassword) echo "input-error"; ?>"/>
            <br />
            <div id="caps-warning" class="error-hidden">
                The CapsLock key seems to be active
            </div>
            <?php
                $errorId = "password-error";
                $errorMessage = $wrongPassword;
                include __DIR__ . "/error.php";
            ?>

            <input id="reg-password-rep-field" type="password"
                name="password-rep"
                placeholder="Repeat password"
                oninput="ajaxValidation('password-rep')"
                onkeypress="return capsLockAlert(event);"
                autocomplete="off"
                class="<?php if ($wrongRepeatedPassword) echo"input-error";?>"/>
            <br />
            <?php
                $errorId = "password-rep-error";
                $errorMessage = $wrongRepeatedPassword;
                include __DIR__ . "/error.php";
            ?>

            <input id="reg-email-field" type="text"
                name="email"
                placeholder="E-mail"
                oninput="ajaxValidation('email')"
                autocomplete="off"
                value="<?= $email ?>"
                class="<?php if ($wrongEmail) echo "input-error"; ?>"/>
            <br />
            <?php
                $errorId = "email-error";
                $errorMessage = $wrongEmail;
                include __DIR__ . "/error.php";
            ?>

            <input id="reg-first-field" type="text" name="first"
                placeholder="First name"
                oninput="ajaxValidation('first')"
                autocomplete="off"
                value="<?= $first ?>"
                class="<?php if ($wrongFirst) echo "input-error"; ?>"/>
            <br />
            <?php
                $errorId = "first-error";
                $errorMessage = $wrongFirst;
                include __DIR__ . "/error.php";
            ?>

            <input id="reg-last-field" type="text" name="last"
                placeholder="Last name"
                oninput="ajaxValidation('last')"
                autocomplete="off"
                value="<?= $last ?>"
                class="<?php if ($wrongLast) echo "input-error"; ?>"/>
            <br />
            <?php
                $errorId = "last-error";
                $errorMessage = $wrongLast;
                include __DIR__ . "/error.php";
            ?>

        </div>

        <!-- right column of the form -->
        <div class="form-right">
            <label for="reg-avatar">
                Profile image
            </label>
            <br />
            <div class="form-contour">
                <input id="reg-avatar-field" type="file"
                    name="avatar" accept="image/*"
                    onchange="imagePreview(event); avatarValidation()" />
                <br />
                <img id="preview-image" />
                <?php
                    $errorId = "avatar-error";
                    $errorMessage = $wrongImage;
                    include __DIR__ . "/error.php";
                ?>
            </div>
        </div>

        <div style="clear: both; width: 0px; height: 0px;"></div>

        <input class="rc-button" id="register-button"
            type="submit" name="register_button"
            value="Register" />
    </div>
</form>
