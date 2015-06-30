<?php
if (!isset($errorImage))
    $errorImage = Null;
if (!isset($title))
    $title = Null;
if (!isset($message))
    $message = Null;
?>
<img class="error-page-image" src="<?= $errorImage ?>" alt="Something broken."/>
<h2 class="error-page-title"><?= $title ?></h2>
<p class="error-page-message">
    <?= $message ?>
</p>
