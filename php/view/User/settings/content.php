<?php
/* Settings form
 */

if (!isset($wrongPassword))
    $wrongPassword = Null;
if (!isset($wrongRepeatedPassword))
    $wrongRepeatedPassword = Null;
if (!isset($confirmationMessage))
    $confirmationMessage = Null;
?>

<script language="javascript"
    src="js/registration_form_validation.js"
    type="text/javascript">
</script>

<h2>Change password</h2>
<?= $confirmationMessage ?>
<form   name="settings-form"
        id="settings-form"
        class="input-form"
        action="settings?cmd=submit"
        method="POST"
        enctype="multipart/form-data">
    <div class="form-contour">
            <!-- unused atm, just to mantain compatibility with the format of
                 the registration form and reuse the same javascript -->
            <input id="reg-username-field" hidden/>
            <input id="reg-email-field" hidden/>
            <input id="reg-first-field" hidden/>
            <input id="reg-last-field" hidden/>

            <!-- actual fields -->
            <input id="reg-password-field" type="password"
                name="password"
                placeholder="New password"
                oninput="ajaxValidation('password')"
                onkeypress="return capsLockAlert(event);"
                autocomplete="off"
                class="<?php if ($wrongPassword) echo "input-error"; ?>"
                autofocus/>
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
                placeholder="Repeat new password"
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

        <div style="clear: both; width: 0px; height: 0px;"></div>

        <input class="rc-button" id="submit-button"
            type="submit" name="submit-button"
            value="<?php echo "Submit change"; ?>" />
    </div>
</form>
