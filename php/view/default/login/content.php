<?php

$root = $_SERVER['DOCUMENT_ROOT'];
?>

<h2>Login</h2>
<form class="input-form" action="/php/control/auth.php" method="post">
    <div class="form-contour">
        <div id="login-mask">
            <input id="username-field" type="text" name="username"
                 placeholder="Username"
                 value="<?= $prevUsername ?>"
                 class="<?php if ($wrongUsername) echo "input-error"; ?>"/>
            <?php
                $errorMessage = $wrongUsername;
                include $root . "/php/view/default/registration/error.php";
            ?>
            <input id="password-field" type="password" name="password"
                placeholder="Password"
                class="<?php if ($wrongPassword) echo "input-error"; ?>"/>
            <?php
                $errorMessage = $wrongPassword;
                include $root . "/php/view/default/registration/error.php";
            ?>
            <input class="rc-button" id="login-button"
                type="submit" name="login_button"
                value="Login" />
        </div>
        <div id="login-button-links">
            <input id="register-button" class="linklike-button"
                type="submit" name="register_button"
                value="Need an account?"/>
            <br />
            <input id="forgotten-button" class="linklike-button"
                type="submit" name="reset_button"
                value="Forgot your password?"/>
        </div>
    </div>
</form>
