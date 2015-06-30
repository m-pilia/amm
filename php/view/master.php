<?php
/**
 *  Master page.
 *  ...
 */

include_once __DIR__ . "/ViewDescriptor.php";
include_once __DIR__ . "/../model/User.php";

if (!session_id())
    session_start();
if (isset($_SESSION["user"]))
    $user = $_SESSION["user"];
?>

<!DOCTYPE html>

<html>

<head>
    <meta http-equiv="content-type" content="text/html;charset=UTF-8" />

    <link rel="stylesheet"
        href="css/stylesheet.css"
        type="text/css" media="all" />
    <link rel="stylesheet"
        href="css/table.css"
        type="text/css" media="all" />
    <link rel="stylesheet"
        href='https://fonts.googleapis.com/css?family=Raleway'
        type='text/css' media="all" />
    <link rel="stylesheet"
        href="https://code.jquery.com/ui/1.11.4/themes/smoothness/jquery-ui.css"
        type='text/css' media="all" />

    <script src="https://code.jquery.com/jquery-2.1.4.min.js"
            type="text/javascript">
    </script>
    <script src="https://code.jquery.com/ui/1.11.4/jquery-ui.js"
            type="text/javascript">
    </script>
    <script src="js/dynamic_view_set.js"
            type="text/javascript">
    </script>

    <title><?= ViewDescriptor::$appName . " - " . $vd->getTitle() ?></title>
</head>

<body id="body">
    <div id="page">

        <!-- header -->
        <header id="header">
            <!-- logo image -->
            <a href="home">
                <img id="logo-image" src="<?= $vd->getLogoImage(); ?>"
                     title="Homepage"
                     alt="Logo." />
            </a>

            <!-- head title -->
            <?php
                $head = $vd->getHead();
                require "$head";
            ?>
        </header>

        <!-- left bar -->
        <div id="sidebar">
            <?php
                $left = $vd->getLeftBarFile();
                require "$left";
            ?>
        </div>

        <!-- sliding section (to show the sidebar menu) -->
        <div id="sliding">
            <!-- trigger to show/hide the sidebar -->
            <img id="trigger"
                 src="images/trigger.svg"
                 alt="trigger"
                 title="Show the sidebar"
                 onclick="sidebarTrigger(event)"/>

            <!-- main content -->
            <div id="content"
                 onclick="closeSidebar()"
                 onmouseleave="closeSidebarAfterTimeCancel()">
                <?php
                    $content = $vd->getContentFile();
                    require "$content";
                ?>
            </div>

            <div style="clear: both; width: 0px; height: 0px;"></div>

            <!-- provides background color to the footer external margin -->
            <div id="footer-wrapper">
                <!-- actual footer -->
                <div id="footer-bar">
                    <?php
                        $footer = $vd->getFooterFile();
                        require "$footer";
                    ?>
                </div>
            </div>
        </div>
    </div>
</body>

<?php
/* mantain the sidebar open if requested */
if (isset($_REQUEST["sidebar"]) && $_REQUEST["sidebar"] == "open")
    echo "<script type=\"text/javascript\">openSidebar();</script>";
?>
</html>
