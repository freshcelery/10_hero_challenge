<?php

/**
 * Created by PhpStorm.
 * User: Keegan
 * Date: 21/05/14
 * Time: 12:33 AM
 */
class config
{
    public $hostname;
    public $username;
    public $password;
    public $database;
    public $prefix;
    public $connector;

    function __construct($hostname = NULL, $username = NULL, $password = NULL, $database = NULL, $prefix = NULL, $connector = NULL)
    {
        $this->hostname = !empty($hostname) ? $hostname : "";
        $this->username = !empty($username) ? $username : "";
        $this->password = !empty($password) ? $password : "";
        $this->database = !empty($database) ? $database : "";
        $this->prefix = !empty($prefix) ? $prefix : "";
        $this->connector = !empty($connector) ? $connector : "mysqli";
    }

    function __destruct()
    {

    }
}

class db
{
    private $connection;
    private $selectdb;
    private $lastQuery;
    private $config;

    function __construct($config)
    {
        $this->config = $config;
    }


    public function openConnection()
    {
        try {
            $this->connection = mysqli_connect($this->config->hostname, $this->config->username, $this->config->password);
            $this->selectdb = mysqli_select_db($this->connection, $this->config->database);
        } catch (exception $e) {
            return $e;
        }
    }

    public function closeConnection()
    {
        try {
            mysqli_close($this->connection);
        } catch (exception $e) {
            return $e;
        }
    }

    public function ecapeString($string)
    {
        return addslashes($string);
    }

    public function query($query)
    {
        try {
            if (empty($this->connection)) {
                $this->openConnection();
                $this->lastQuery = mysqli_query($this->connection, $this->ecapeString($query));
                $this->closeConnection();

                return $this->lastQuery;
            } else {
                $this->lastQuery = mysqli_query($this->connection, $this->ecapeString($query));

                return $this->lastQuery;
            }
        } catch (exception $e) {
            return $e;
        }
    }

    public function lastQuery()
    {
        return $this->lastQuery;
    }

    public function fetchAssoc($result)
    {
        try {
            return mysqli_fetch_assoc($result);
        } catch (exception $e) {
            return $e;
        }
    }

    public function fetchArray($result)
    {
        try {
            return mysqli_fetch_array($result);
        } catch (exception $e) {
            return $e;
        }
    }
}
