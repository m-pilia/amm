<?php

if (!isset($image))
    $image = Null;
if (!isset($alt))
    $alt = Null;
if (!isset($title))
    $title = Null;
if (!isset($message))
    $message = Null;

if ($image)
    echo "<img class=\"generic-page-image\" src=\"$image\" alt=\"$alt\"/>";
?>
<h2 class="generic-page-title"><?= $title ?></h2>
<p class="generic-page-message">
    <?= $message ?>
</p>
