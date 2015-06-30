<h2><?php echo $vd->getTitle(); ?></h2>

<script src="js/resource_management.js"
        type="text/javascript">
</script>
<form class="container-wrapper"
      method="POST"
      action="resourceManager?cmd=submit">
    <div class="form-contour">
        <div id="resources" class="view-table container">
            <div class="view-row container-heading">
                <span><!-- column of the edit button --></span>
                <span>Resource</span>
                <span>Delete?</span>
            </div>
            <?php
            foreach ($resources as $r) {
                $name = $r->getName();
                $id = $r->getId();

                echo <<<EOF
                <div class="resource-box view-row">
                    <a id="a-$id" onclick="enableResourceInput($id)">
                        Edit
                    </a>
                    <span>
                        <input type="text"
                               name="$id"
                               value="$name"
                               disabled="disabled"/>
                    </span>
                    <span>
                        <input type="checkbox" name="$id-del" value="1"/>
                    </span>
                </div>
EOF;
            }
            ?>
        </div>

        <br />
        <input id="add-button"
               type="button"
               onclick="newResource()"
               value="New resource"/>

        <br />
        <input id="save-button"
               type="submit"
               class="rc-button"
               value="Save"/>
    </div>
</form>
