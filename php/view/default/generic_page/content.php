<?php
if (isset($image) && $image != Null)
    echo <<<EOF
        <img class="generic-page-image" src="$image" alt="$alt"/>
EOF;
?>
<h2 class="generic-page-title"><?= $title ?></h2>
<p class="generic-page-message">
    <?= $message ?>
</p>
