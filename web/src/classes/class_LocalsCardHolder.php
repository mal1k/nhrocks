<?

    # ----------------------------------------------------------------------------------------------------
    # * FILE: /classes/class_LocalsCardHolder.php
    # ----------------------------------------------------------------------------------------------------

    class LocalsCardHolder extends Handle {

        var $account_id;
        var $session_id;
        var $entered;
        var $active;

        public function __construct($var="") {
            if (is_numeric($var) && ($var)) {
                $db = db_getDBObject(DEFAULT_DB, true);
                $sql = "SELECT * FROM Locals_Card_Holders WHERE account_id = $var";
                $row = mysqli_fetch_array($db->query($sql));
                $this->makeFromRow($row);
            } else {
                if (!is_array($var)) {
                    $var = array();
                }
                $this->makeFromRow($var);
            }
        }

        function makeFromRow($row="") {

            $this->account_id = ($row["account_id"]) ? $row["account_id"] : ($this->account_id ? $this->account_id : 0);
            $this->entered = ($row["entered"]) ? $row["entered"] : ($this->entered ? $this->entered : '');
            $this->active = ($row["active"]) ? $row["active"] : ($this->active ? $this->active : 0);
            $this->session_id = ($row["session_id"]) ? $row["session_id"] : ($this->session_id ? $this->session_id : 0);

        }

        function Update() {

            $dbObj = db_getDBObject(DEFAULT_DB, true);

            $this->prepareToSave();

            $sql = "UPDATE Locals_Card_Holders SET"
                . " entered = $this->entered,"
                . " active = $this->active,"
                . " session_id = $this->session_id"
                . " WHERE account_id = $this->account_id";
            $dbObj->query($sql);

            $this->account_id = ((is_null($___mysqli_res = mysqli_insert_id($dbObj->link_id))) ? false : $___mysqli_res);

            $this->prepareToUse();

        }

        function Save() {

            $dbObj = db_getDBObject(DEFAULT_DB, true);

            $this->prepareToSave();

            $sql = "INSERT INTO Locals_Card_Holders"
                . " (account_id, session_id, entered, active) "
                . " VALUES"
                . " ("
                . " $this->account_id,"
                . " $this->session_id,"
                . " $this->entered,"
                . " $this->active"
                . " )";

            $dbObj->query($sql);

            $this->account_id = ((is_null($___mysqli_res = mysqli_insert_id($dbObj->link_id))) ? false : $___mysqli_res);

            $this->prepareToUse();

        }

        function Delete() {
            $dbMain = db_getDBObject(DEFAULT_DB, true);
            $dbObj = db_getDBObjectByDomainID(SELECTED_DOMAIN_ID, $dbMain);
            $sql = "DELETE FROM Locals_Card_Holders WHERE id = $this->account_id";
            $dbObj->query($sql);
        }

    }

?>
