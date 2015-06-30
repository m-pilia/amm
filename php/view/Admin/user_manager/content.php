<h2><?php echo $vd->getTitle(); ?></h2>

<form class="container-wrapper"
      method="POST"
      action="userManager?cmd=submit">
    <div class="form-contour">
        <div id="users-table" class="view-table container">
            <!-- table headings -->
            <div class="view-row container-heading">
                <span><!-- placeholder above the avatars --></span>
                <span>Username</span>
                <span>First</span>
                <span>Last</span>
                <span>Email</span>
                <span>Events</span>
                <span>Admin?</span>
                <span>Delete?</span>
            </div>
            <?php
            foreach ($users as $u) {
                $username = $u->getUsername();
                $id = $u->getId();
                $avatar = $u->getAvatar();
                $first = $u->getFirst();
                $last = $u->getLast();
                $email = $u->getEmail();
                $events = $u->getCreatedEvents();

                /* the checkbox is checked for the admins */
                $isAdmin = $u->getRole() == Role::ADMIN ? "checked" : "";

                /* the edit is disabled for the user's self account */
                $selfRole = $id == $_SESSION['user']->getId() ? "disabled" : "";

                /* show a row with the informations for the user */
                echo <<<EOF
                <div class="users-box view-row">
                    <img id="avatar" src="$avatar" alt="$username's avatar"/>
                    <span>$username</span>
                    <span>$first</span>
                    <span>$last</span>
                    <span>$email</span>
                    <span>$events</span>
                    <span>
                        <input type="checkbox"
                               name="$id-role"
                               value="1"
                               $isAdmin
                               $selfRole/>
                    </span>
                    <span>
                        <input type="checkbox"
                               name="$id-del"
                               value="1"
                               $selfRole/>
                    </span>
                </div>
EOF;
            }
            ?>
        </div>

        <br />
        <input id="save-button"
               type="submit"
               class="rc-button"
               value="Save"/>
    </div>
</form>
