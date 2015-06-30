<?php
/**
 * \brief This class represents a bookable resource.
 * @author Martino Pilia <martino.pilia@gmail.com>
 * @date 2015-06-26
 */
class Resource {

    private $id;   /*!< int    Resource id in the database. */
    private $name; /*!< string Name of the resource. */

    /**
     * \brief Construct a Resource object.
     * @param $name  string Name of the resource.
     */
    public function __construct($id, $name) {
        $this->id = $id;
        $this->name = $name;
    }

    /**
     * \brief Getter for id.
     * @return int The resource id in the database.
     */
    public function getId() {
        return $this->id;
    }

    /**
     * \brief Getter for name.
     * @return string The name of the resource.
     */
    public function getName() {
        return $this->name;
    }

    /**
     * \brief Setter for name.
     * @param string $name Resource name.
     * @return Resource This object.
     */
    public function setName($name) {
        $this->name = $name;
        return $this;
    }

    /**
     * \brief Compare two Resource objects according to their name.
     * @param  Event  $first  First event to be compared.
     * @param  Event  $second Second event to be compared.
     * @return int    A value lesser than zero if $first < $second, equal to
     *                zero when equals and greater than zero when
     *                $first > $second.
     */
    public static function compare(Resource $first, Resource $second) {
        return strcmp($first->name, $second->name);
    }

    /**
     * \brief Get an array containing the resources.
     * @return array Contains all the resources (as Resource objects).
     */
    public static function getResources() {
        $resources = array();

        $mysqli = Database::getInstance()->connect(); /* db connection */

        /* get resources */
        $result = $mysqli->query("SELECT Id, Name FROM Resources");
        if ($mysqli->errno > 0)
            throw new DbQueryException($mysqli->errno,
                    $mysqli->error, "Query error");

        while ($row = $result->fetch_row())
            $resources[] = new Resource($row[0], $row[1]);

        $mysqli->close(); /* close db connection */

        /* sort resources into alphabetical order by name */
        usort($resources, 'Resource::compare');

        return $resources;
    }

    /**
     * \brief Update the resources in the database.
     * @param  array $add Array of strings containing the names of the
     *                    resources to be added.
     * @param  array $del Array of Resource objects to be removed.
     * @param  array $upd Array of Resource objects to be renamed.
     *
     * @note   The data is validated, the new or updated names are trimmed and
     *         blank strings are ignored.
     */
    public static function updateResources($add, $del, $upd) {
        require __DIR__ . "/../settings.php";

        /* open database connection and start a transation */
        $mysqli = Database::getInstance()->connect();
        $mysqli->autocommit(False);

        /* add new resources */
        foreach ($add as $a) {
            /* note: $a is a string, not a Resource object */
            /* trim whitespace and multiple spaces and set max len */
            $a = mb_strimwidth(trim($a), 0, $maxResourceNameLen);
            $a = preg_replace('/\s+/', ' ', $a);

            /* ignore blank strings */
            if ($a == "")
                continue;

            /* add resource in the database (ignore duplicate names) */
            $stmt = $mysqli->stmt_init();
            $stmt->prepare("INSERT IGNORE INTO Resources (Name) VALUES (?)");
            if (!$stmt) {
                $mysqli->rollback();
                throw new DbQueryException($stmt->errno,
                        $stmt->error, "Stmt prepare");
            }

            $bind = $stmt->bind_param("s", $a);
            if (!$bind) {
                $mysqli->rollback();
                throw new DbQueryException($stmt->errno,
                        $stmt->error, "Stmt bind param");
            }

            if (!$stmt->execute()) {
                $mysqli->rollback();
                throw new DbQueryException($stmt->errno,
                        $stmt->error, "Stmt execute");
            }
            $stmt->close();
        }

        /* delete old resources */
        foreach ($del as $d) {
            $id = $d->getId(); /* id of the resource to be removed */

            /* delete all the events for the resource */
            $stmt = $mysqli->stmt_init();
            $stmt->prepare("DELETE FROM Events WHERE Resource_id = ?");
            if (!$stmt) {
                $mysqli->rollback();
                throw new DbQueryException($stmt->errno,
                        $stmt->error, "Stmt prepare");
            }

            $bind = $stmt->bind_param("d", $id);
            if (!$bind) {
                $mysqli->rollback();
                throw new DbQueryException($stmt->errno,
                        $stmt->error, "Stmt bind param");
            }

            if (!$stmt->execute()) {
                $mysqli->rollback();
                throw new DbQueryException($stmt->errno,
                        $stmt->error, "Stmt execute");
            }
            $stmt->close();

            /* delete resource */
            $stmt = $mysqli->stmt_init();
            $stmt->prepare("DELETE FROM Resources WHERE Id = ?");
            if (!$stmt) {
                $mysqli->rollback();
                throw new DbQueryException($stmt->errno,
                        $stmt->error, "Stmt prepare");
            }

            $bind = $stmt->bind_param("d", $id);
            if (!$bind) {
                $mysqli->rollback();
                throw new DbQueryException($stmt->errno,
                        $stmt->error, "Stmt bind param");
            }

            if (!$stmt->execute()) {
                $mysqli->rollback();
                throw new DbQueryException($stmt->errno,
                        $stmt->error, "Stmt execute");
            }
            $stmt->close();
        }

        /* update renamed resources */
        foreach ($upd as $u) {
            $name = $u->getName(); /* new name for the resource */
            $id = $u->getId(); /* id of the resource to be renamed */

            /* trim whitespace and multiple spaces and set max len */
            $name = trim($name);
            $name = preg_replace('/\s+/', ' ', $name);
            $name = mb_strimwidth($name, 0, $maxResourceNameLen);

            /* ignore blank strings */
            if ($name == "")
                continue;

            /* update the name in the database (ignore duplicate names)*/
            $stmt = $mysqli->stmt_init();
            $stmt->prepare("UPDATE IGNORE Resources SET Name = ? WHERE Id = ?");
            if (!$stmt) {
                $mysqli->rollback();
                throw new DbQueryException($stmt->errno,
                        $stmt->error, "Stmt prepare");
            }

            $bind = $stmt->bind_param("sd", $name, $id);
            if (!$bind) {
                $mysqli->rollback();
                throw new DbQueryException($stmt->errno,
                        $stmt->error, "Stmt bind param");
            }

            if (!$stmt->execute()) {
                $mysqli->rollback();
                throw new DbQueryException($stmt->errno,
                        $stmt->error, "Stmt execute");
            }
            $stmt->close();
        }

        /* all seems ok, commit changes to the database */
        if (!$mysqli->commit()) {
            $mysqli->rollback();
            throw new DbQueryException($mysqli->errno,
                    $mysqli->error, "Commit");
        }

        $mysqli->autocommit(True);
        $mysqli->close(); /* close db connection */
    }
}
?>
