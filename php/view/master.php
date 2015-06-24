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
    <script language="javascript"
        src="https://code.jquery.com/jquery-2.1.4.min.js"
        type="text/javascript">
    </script>
    <script language="javascript"
        src="js/dynamic_view_set.js"
        type="text/javascript">
    </script>

    <title><?= ViewDescriptor::$appName . " - " . $vd->getTitle() ?></title>
</head>

<body id="body">
    <div id="page">

        <!-- header -->
        <header id="header">
            <!-- logo image -->
            <div id="logo">
                <a href="home">
                    <img src="<?= $vd->getLogoImage(); ?>" alt="Logo." />
                </a>
            </div>

            <!-- head title -->
            <div id="head">
                <?php
                    $head = $vd->getHead();
                    require "$head";
                ?>
            </div>
        </header>

        <!-- left bar -->
        <div id="sidebar">
            <?php
                $left = $vd->getLeftBarFile();
                require "$left";
            ?>
        </div>

        <!-- main content -->
        <div id="content">
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
</body>
</html>
