<?php
$avatar = $user->getAvatar();
$name = $user->getFirst() . " " . $user->getLast();
?>

<ul id="sidebar-list">
    <p id="sidebar-name"><?= $name ?></p>
    <img id="avatar" src="<?= $avatar ?>" alt="Avatar image."/>
    <!-- '?sidebar=open' mantains the sidebar open in the new page -->
    <li class="sidebar-item"><a href="home">Home</a></li>
    <li class="sidebar-item"><a href="settings">Settings</a></li>
    <li class="sidebar-item"><a href="about">About</a></li>
    <li class="sidebar-item"><a href="logout">Logout</a></li>
</ul>
