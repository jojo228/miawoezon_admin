<?php
require 'reconfig.php';

class Estate {
    private $conn;

    // Constructor to initialize the connection
    public function __construct() {
        $this->conn = $GLOBALS['rstate'];
    }

    // Login function for different tables
    function restatelogin($username, $password, $tblname) {
        $q = "";
        if ($tblname == 'admin') {
            $q = "SELECT * FROM $tblname WHERE username='$username' AND password='$password'";
        } else if ($tblname == 'restate_details') {
            $q = "SELECT * FROM $tblname WHERE email='$username' AND password='$password'";
        } else {
            $q = "SELECT * FROM $tblname WHERE email='$username' AND password='$password' AND status=1";
        }
        return $this->conn->query($q)->num_rows;
    }

    // Insert data into a table
    function restateinsertdata($field, $data, $table) {
        $field_values = implode(',', $field);
        $data_values = implode("','", $data);
        $sql = "INSERT INTO $table($field_values) VALUES ('$data_values')";
        $result = $this->conn->query($sql);
        return $result;
    }

    // Insert data into a table and return the last inserted ID
    function restateinsertdata_id($field, $data, $table) {
        $field_values = implode(',', $field);
        $data_values = implode("','", $data);
        $sql = "INSERT INTO $table($field_values) VALUES ('$data_values')";
        $result = $this->conn->query($sql);
        return $this->conn->insert_id;
    }

    // Insert data using API
    function restateinsertdata_Api($field, $data, $table) {
        $field_values = implode(',', $field);
        $data_values = implode("','", $data);
        $sql = "INSERT INTO $table($field_values) VALUES ('$data_values')";
        $result = $this->conn->query($sql);
        return $result;
    }

    // Insert data using API and return last inserted ID
    function restateinsertdata_Api_Id($field, $data, $table) {
        $field_values = implode(',', $field);
        $data_values = implode("','", $data);
        $sql = "INSERT INTO $table($field_values) VALUES ('$data_values')";
        $result = $this->conn->query($sql);
        return $this->conn->insert_id;
    }

    // Update data in a table
    function restateupdateData($field, $table, $where) {
        $cols = array();
        foreach ($field as $key => $val) {
            if ($val != NULL) {
                $cols[] = "$key = '$val'";
            }
        }
        $sql = "UPDATE $table SET " . implode(', ', $cols) . " $where";
        $result = $this->conn->query($sql);
        return $result;
    }

    // Update data allowing NULL values
    function restateupdateDatanull_Api($field, $table, $where) {
        $cols = array();
        foreach ($field as $key => $val) {
            if ($val != NULL) {
                $cols[] = "$key = '$val'";
            } else {
                $cols[] = "$key = NULL";
            }
        }
        $sql = "UPDATE $table SET " . implode(', ', $cols) . " $where";
        $result = $this->conn->query($sql);
        return $result;
    }

    // Update single field
    function restateupdateData_single($field, $table, $where) {
        $sql = "UPDATE $table SET $field $where";
        $result = $this->conn->query($sql);
        return $result;
    }

    // Delete data from a table
    function restaterestateDeleteData($where, $table) {
        $sql = "DELETE FROM $table $where";
        $result = $this->conn->query($sql);
        return $result;
    }

    // Delete data using API
    function restateDeleteData_Api($where, $table) {
        $sql = "DELETE FROM $table $where";
        $result = $this->conn->query($sql);
        return $result;
    }
}
?>
