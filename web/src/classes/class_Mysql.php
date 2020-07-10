<?php

/*==================================================================*\
######################################################################
#                                                                    #
# Copyright 2018 Arca Solutions, Inc. All Rights Reserved.           #
#                                                                    #
# This file may not be redistributed in whole or part.               #
# eDirectory is licensed on a per-domain basis.                      #
#                                                                    #
# ---------------- eDirectory IS NOT FREE SOFTWARE ----------------- #
#                                                                    #
# http://www.edirectory.com | http://www.edirectory.com/license.html #
######################################################################
\*==================================================================*/

# ----------------------------------------------------------------------------------------------------
# * FILE: /classes/class_mysql.php
# ----------------------------------------------------------------------------------------------------

class mysql
{
    public $result = '';

    public function __construct($DB_KEY)
    {
        $this->_reset_properties();
        $this->SERVER_NAME = $_SERVER['SERVER_NAME'];
        $this->PHP_SELF = $_SERVER['PHP_SELF'];
        $this->db_key = $DB_KEY;
        $this->db_host = constant("_".$DB_KEY."_HOST");
        $this->db_user = constant("_".$DB_KEY."_USER");
        $this->db_pass = constant("_".$DB_KEY."_PASS");
        $this->db_name = constant("_".$DB_KEY."_NAME");
        $this->db_email = constant("_".$DB_KEY."_EMAIL");
        $this->db_debug = constant("_".$DB_KEY."_DEBUG");
        $this->mysql_error = false;
        $this->expire_connection = mktime(date("G"), date("i"), date("s") + MYSQL_TIMEOUT, date("n"), date("j"),
            date("Y"));

        if ($this->db_debug == "display") {
            $this->db_debug = 1;
        } else {
            $this->db_debug = 0;
        }

        /*
         * Check connection in Connection Pool
         */
        $link = ConnectionPool::instance()->getConnection($this->db_name);
        if (!$link) {
            $this->link_id = ($GLOBALS["___mysqli_ston"] = mysqli_connect($this->db_host,  $this->db_user,  $this->db_pass));

            // Set mysql Parameters
            mysqli_query( $this->link_id, "SET NAMES 'utf8'");
            mysqli_query( $this->link_id, 'SET character_set_connection=utf8');
            mysqli_query( $this->link_id, 'SET character_set_client=utf8');
            mysqli_query( $this->link_id, 'SET character_set_results=utf8');
            mysqli_query( $this->link_id, "SET SESSION time_zone = '{$this->getOffSet()}'");

            if ($this->link_id) {
                $this->select_db_name = mysqli_select_db( $this->link_id, $this->db_name);
                if (!$this->select_db_name) {
                    $this->_handle_error("constructor: select_db");
                }
            } else {
                $this->_handle_error("constructor: mysql_connect");
            }
            ConnectionPool::instance()->registerConnection($this, $this->db_name);
        } else {
            $this->link_id = $link;
        }
    }

    function getOffSet()
    {
        $now = new DateTime();
        $mins = $now->getOffset() / 60;

        $sgn = ($mins < 0 ? -1 : 1);
        $mins = abs($mins);
        $hrs = floor($mins / 60);
        $mins -= $hrs * 60;

        return sprintf('%+d:%02d', $hrs * $sgn, $mins);
    }

    # ----------------------------------------------------------------------------------------------------
    # external methods
    # ----------------------------------------------------------------------------------------------------
    function getmicrotime()
    {
        list($usec, $sec) = explode(" ", microtime());

        return ((float)$usec + (float)$sec);
    }


    function query(&$query, $resultmode = MYSQLI_STORE_RESULT)
    {
        $this->mysql_error = false;

        $result = mysqli_query( $this->link_id, $query, $resultmode);

        if (!$result) {
            $this->_handle_error($query);
        }

        $this->result = $result;

        return $this->result;
    }

    /**
     * Method to close connection
     * @copyright Copyright 2018 Arca Solutions, Inc.
     * @author Arca Solutions, Inc.
     * @name close ()
     */
    function close()
    {
        if (!((is_null($___mysqli_res = mysqli_close($this->link_id))) ? false : $___mysqli_res)) {
            $subject = "ERROR: http://".$this->SERVER_NAME.$this->PHP_SELF;
            $message = "\n\n Error closing connection\n\n";
            if ($this->link_id) {
                $message .= " Errno: ".mysqli_errno($this->link_id)."\n";
                $message .= " Error: ".mysqli_error($this->link_id)."\n";
            }
            $message .= "_SERVER data\n";
            $server_values = [
                'REMOTE_ADDR',
                'REMOTE_PORT',
                'SCRIPT_FILENAME',
                'REQUEST_METHOD',
                'QUERY_STRING',
                'REQUEST_URI',
            ];
            foreach ($server_values as $name) {
                $message .= sprintf("%15s : %s\n", $name, $_SERVER[$name]);
            }

            if ($this->db_debug) {
                echo "<PRE>$message</PRE>\n";
            } else {
                echo "Database Error. System Administrator has been notified and this problem will be solved as soon as possible. We are sorry for the inconvenience.";
            }
        } else {
            ConnectionPool::instance()->unsetConnection($this->db_name);
        }
    }


    # ----------------------------------------------------------------------------------------------------
    # convinence method - returns number of rows for a query
    # good for doing counts
    # ----------------------------------------------------------------------------------------------------
    function numRowsQuery(&$query)
    {
        $result = $this->query($query);

        return mysqli_num_rows($result);
    }

    /*
     * optimized method. because the following query is code optmized in mysql for faster performance
     * (see mysql docs)
     */
    function getRowCount($table, $domain_id = false)
    {
        $sql = "SELECT COUNT(*) as total FROM $table";
        if ($table == "Account" || $table == "Location_1" || $table == "Location_2" || $table == "Location_3" || $table == "Location_4" || $table == "Location_5") { //Account export, force main DB
            $db = db_getDBObject(DEFAULT_DB, true);
        } else {
            if ($domain_id) { //others items export, use domain DB
                $dbMain = db_getDBObject(DEFAULT_DB, true);
                $db = db_getDBObjectByDomainID(defined("SELECTED_DOMAIN_ID") ? SELECTED_DOMAIN_ID : $domain_id,
                    $dbMain);
            } else {
                $db = db_getDBObject(); //front
            }
        }

        if ($r = $db->query($sql)) {
            $row = mysqli_fetch_assoc($r);

            return $row["total"];
        }
    }

    function getRowCountSQL($sql)
    {
        $db = db_getDBObject();
        if ($r = $db->query($sql)) {
            $row = mysqli_fetch_array($r);

            return $row[0];
        }
    }

    # ----------------------------------------------------------------------------------------------------
    # internal methods
    # ----------------------------------------------------------------------------------------------------
    function _handle_error($query)
    {
        $this->mysql_error = mysqli_error($this->link_id);

        $subject = "ERROR: http://".$this->SERVER_NAME.$this->PHP_SELF;
        $message = "\n\n$subject\n\n";
        $message .= "Query: $query\n\n";
        if ($this->link_id) {
            $message .= " Errno: ".mysqli_errno($this->link_id)."\n";
            $message .= " Error: ".$this->mysql_error."\n";
        }
        $message .= "_SERVER data\n";
        $server_values = [
            'REMOTE_ADDR',
            'REMOTE_PORT',
            'SCRIPT_FILENAME',
            'REQUEST_METHOD',
            'QUERY_STRING',
            'REQUEST_URI',
        ];
        foreach ($server_values as $name) {
            $message .= sprintf("%15s : %s\n", $name, $_SERVER[$name]);
        }
        if ($this->db_debug) {
            echo "<PRE>$message</PRE>\n";
        } else {
            echo "Database Error. System Administrator has been notified and this problem will be solved as soon as possible. We are sorry for the inconvenience.";
        }
    }

    function _reset_properties()
    {

        $this->SERVER_NAME = "";
        $this->PHP_SELF = "";
        $this->db_email = "";
        $this->db_host = "";
        $this->db_user = "";
        $this->db_pass = "";
        $this->db_name = "";
        $this->db_debug = "";
        $this->link_id = "";
        $this->result = "";
        $this->select_db_name = "";
    }

    function getMaxValue($table, $field)
    {
        $sql = "SELECT MAX($field) as max_value FROM $table";
        $r = $this->query($sql);
        $max_value_arr = mysqli_fetch_assoc($r);
        if ($max_value_arr) {
            return $max_value_arr["max_value"];
        } else {
            return false;
        }
    }
}
