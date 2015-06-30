<?php
if (!isset($prevUsername))
    $prevUsername = Null;
if (!isset($wrongUsername))
    $wrongUsername = Null;
if (!isset($wrongPassword))
    $wrongPassword = Null;
?>

<h2>Login</h2>
<form class="input-form" action="login?cmd=auth" method="post">
    <div class="form-contour">
        <div id="login-mask">
            <input id="username-field" type="text" name="username"
                 placeholder="Username"
                 value="<?= $prevUsername ?>"
                 class="<?php if ($wrongUsername) echo "input-error"; ?>"/>
            <br />
            <?php
                $errorMessage = $wrongUsername;
                include __DIR__ . "/../registration/error.php";
            ?>
            <input id="password-field" type="password" name="password"
                placeholder="Password"
                onkeypress="return capsLockAlert(event);"
                class="<?php if ($wrongPassword) echo "input-error"; ?>"
                <?php if ($wrongPassword) echo "autofocus"; ?>/>
            <br />
            <div id="caps-warning" class="error-hidden">
                The CapsLock key seems to be active
            </div>
            <?php
                $errorMessage = $wrongPassword;
                include __DIR__ . "/../registration/error.php";
            ?>
            <input class="rc-button"
                type="submit" name="login_button"
                value="Login" />
        </div>

        <a id="register-link"
           onclick="window.open('registration?username=' + $('#username-field').val(), '_self')">
            Need an account?
        </a>
        <br />
        <a id="forgotten-link"
           onclick="window.open('reset?username=' + $('#username-field').val(), '_self')">
            Forgot your password?
        </a>
    </div>
</form>
