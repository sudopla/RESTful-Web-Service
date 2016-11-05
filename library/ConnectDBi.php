<?php


class ConnectDBi {

    private $_dbhost;
    private $_dbuser;
    private $_dbpass;
    private $_dbname;

    protected $_dbc;


    public function __construct(){
        $this->_dbhost = 'localhost';
        //$this->_dbhost = 'server_address';
        $this->_dbuser = 'user';
        $this->_dbpass = 'password';
        $this->_dbname = 'eg1stfpw';

        $this->_dbc = mysqli_connect($this->_dbhost, $this->_dbuser, $this->_dbpass, $this->_dbname);

        if (!$this->_dbc) {
            throw new Exception(mysqli_connect_error());
        }
    }


    public function Insert($query) {
        $result = mysqli_query($this->_dbc, $query);
        $id = mysqli_insert_id($this->_dbc);

        if (!$result) {
            throw new Exception(mysqli_error($this->_dbc));
        }

        return $id;
    }


    public function Execute($query) {
        $result = mysqli_query($this->_dbc, $query);

        if (!$result) {
            throw new Exception(mysqli_error($this->_dbc));
        }

        return $result;
    }


    public function Close_Connection(){
        mysqli_close($this->_dbc);
    }
} 