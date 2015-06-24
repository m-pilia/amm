<?php

/**
 * \brief Class containing variable content for master page.
 *
 * @author Martino Pilia <martino.pilia@gmail.com>
 * @date 2015-04-24
 */
class ViewDescriptor {

    public static $login = "login";
    public static $logout = "logout";
    public static $home = "home";
    public static $about = "about";
    public static $error = "error";
    public static $settings = "settings";
    public static $registration = "registration";
    public static $registered = "registered";
    public static $appName = "RBA";

    /**
     * Document root
     * @var string
     */
    private $root;

    /**
     * View root in the MVC
     * @var string
     */
    private $viewRoot;

    /**
     * Default role components folder
     * @var string
     */
    private $defaultRole;

    /**
     * Page type
     * @var string
     */
    private $page;

    /**
     * Page title
     * @var string
     */
    private $title;

    /**
     * File for header.
     * @var string
     */
    private $head;

    /**
     * File for logo.
     * @var string
     */
    private $logoImage;

    /**
     * File for left sidebar content.
     * @var string
     */
    private $leftBarFile;

    /**
     * File for content section's content.
     * @var string
     */
    private $contentFile;

    /**
     * File for footer content.
     * @var string
     */
    private $footerFile;

    /**
     * \brief Set constants and initialize values to the generic page
     * for a visitor.
     */
    public function __construct() {
        $this->root = $_SERVER['DOCUMENT_ROOT'];
        if (substr($this->root, -1) != "/")
            $this->root .= "/";
        $this->viewRoot = $this->root . "php/view";
        $this->page = "/generic_page";
        $this->defaultRole = "/default";

        $this->logoImage = '/images/logo.svg';
        $this->contentFile = $this->setComponent('/content.php', '/default');
        $this->head = $this->setComponent('/head.php', '/default');
        $this->leftBarFile = $this->setComponent('/leftBar.php', '/default');
        $this->footerFile = $this->setComponent('/footer.php', '/default');
    }

    /**
     * \brief
     * @param cmp Name of the component file (e.g. '/content.php').
     * @param role Role of the user the page is destined to.
     * @return The path of the file to be included in the master page.
     *
     * This function, given the page kind and the user role as parameters,
     * searches the right filename for a component of the page, (i.e. the
     * name of the php script to include in each master page section).
     * The component files are searched along a hierarchical tree, with the
     * user role as first level and the page type as second level:
     *
     *   /view
     *     |--> /default
     *     |     |--> /generic_page
     *     |     |     |--> head.php
     *     |     |     |--> ...
     *     |     |
     *     |     |--> /home
     *     |     |     |--> head.php
     *     |     |     |--> ...
     *     |     |
     *     |     |--> ...
     *     |
     *     |--> /User
     *     |     |--> /generic_page
     *     |     |     |--> head.php
     *     |     |     |--> ...
     *     |     |
     *     |     |--> home
     *     |     |     |--> head.php
     *     |     |     |--> ...
     *     |     |
     *     |     |--> ...
     *     |
     *     |--> /Admin
     *           |--> /generic_page
     *           |     | --> head.php
     *           |     | --> ...
     *           |
     *           |--> ...
     *
     * With such a hierarchy, the homepage of an User will probably share some
     * components with the about page for the same User, e.g. the head.php
     * file, while having different content.php file, and maybe it will share
     * some components with a visitor user too (e.g. the footer).
     *
     * In this file tree, only the needed files for the components are written
     * and put in their right locations, while the components unchanged
     * respect to the more generic user/page type are just omitted. This method
     * does the job to find the right file everytime is needed.
     *
     * When a page is set, the component file is found with a cascade search.
     * It is searched in its most specific location first (i.e the specified
     * role and page type). If the file is not found, a fallback is searched
     * in the generic page for the specified role. If this is not found too,
     * the search is repeated for the visitor role in the specified page type
     * and, when even this file does not exist, the generic page component for
     * the visitor role is used.
     */
    private function setComponent($cmp, $role) {
        /* search in /role/page */
        $file = $this->viewRoot . $role . $this->page . $cmp;
        if (file_exists($file))
            return $file;

        /* if fails, search in /User/page */
        $file = $this->viewRoot . "/User" . $this->page . $cmp;
        if (file_exists($file))
            return $file;

        /* if fails, search in /role/generic_page */
        $file = $this->viewRoot . $role . "/generic_page" . $cmp;
        if (file_exists($file))
            return $file;

        /* if fails, search in /User/generic_page */
        $file = $this->viewRoot . "/User" . "/generic_page" . $cmp;
        if (file_exists($file))
            return $file;

        /* if fails, search in /default/page */
        $file = $this->viewRoot . $this->defaultRole . $this->page . $cmp;
        if (file_exists($file))
            return $file;

        /* if fails, use /default/generic_page */
        $file = $this->viewRoot . $this->defaultRole . "/generic_page" . $cmp;
        return $file;
    }

    /*
     * Getter for root
     */
    public function getRoot() {
        return $this->root;
    }

    /*
     * Getter for page
     */
    public function getPage() {
        return $this->page;
    }

    /*
     * Setter for page
     *
     * This method sets the page and all the page components.
     */
    public function setPage($page, $role) {
        if (isset($page) && $page != Null) {
            $this->page = "/" . $page;
        }
        else {
            $this->page = "/generic_page";
            return $this;
        }

        if (isset($role) && $role != Null)
            $role = "/" . $role;
        else
            $role = $this->defaultRole;

        $this->logoImage = '/images/logo.svg';

        $this->contentFile = $this->setComponent('/content.php', $role);
        $this->head = $this->setComponent('/head.php', $role);
        $this->leftBarFile = $this->setComponent('/leftBar.php', $role);
        $this->footerFile = $this->setComponent('/footer.php', $role);

        return $this;
    }

    /*
     * Setter for title
     */
    public function setTitle($title) {
        $this->title  = $title;
        return $this;
    }

    /*
     * Getter for title
     */
    public function getTitle() {
        return $this->title;
    }

    /*
     * Setter for head
     */
    public function setHead($head) {
        $this->head = $this->root . $head;
        return $this;
    }

    /*
     * Getter for head
     */
    public function getHead() {
        return $this->head;
    }

    /*
     * Setter for logoImage
     */
    public function setLogoImage($logoImage) {
        $this->logoImage = $this->root . $logoImage;
        return $this;
    }

    /*
     * Getter for logoImage
     */
    public function getLogoImage() {
        return $this->logoImage;
    }

    /*
     * Setter for leftBarFile
     */
    public function setLeftBarFile($leftBarFile) {
        $this->leftBarFile = $this->root . $leftBarFile;
        return $this;
    }

    /*
     * Getter for leftBarFile
     */
    public function getLeftBarFile() {
        return $this->leftBarFile;
    }

    /*
     * Setter for rightBarFile
     */
    public function setRightBarFile($rightBarFile) {
        $this->rightBarFile = $this->root . $rightBarFile;
        return $this;
    }

    /*
     * Getter for rightBarFile
     */
    public function getRightBarFile() {
        return $this->rightBarFile;
    }

    /*
     * Setter for contentFile
     */
    public function setContentFile($contentFile) {
        $this->contentFile = $this->root . $contentFile;
        return $this;
    }

    /*
     * Getter for contentFile
     */
    public function getContentFile() {
        return $this->contentFile;
    }

    /*
     * Setter for footerFile
     */
    public function setFooterFile($footerFile) {
        $this->footerFile = $this->root . $footerFile;
        return $this;
    }

    /*
     * Getter for footerFile
     */
    public function getFooterFile() {
        return $this->footerFile;
    }
}

?>
