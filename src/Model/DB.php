<?php

namespace IPS\Model;

class DB
{
    protected static $instance = null;
    protected $pdo = null;
    protected $stmt;

    public static function instance()
    {
        if(is_null(static::$instance)) {
            static::$instance = new static();
        }
        return static::$instance;
    }

    protected function __construct()
    {
        $this->pdo = new \PDO('sqlite:' . __DIR__ . '/../../twitch.db');
        $this->pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
        $this->pdo->setAttribute(\PDO::ATTR_DEFAULT_FETCH_MODE, \PDO::FETCH_ASSOC);
    }
    
    public function execute($query, $params = [])
    {
        $this->stmt = $this->pdo->prepare($query);
        $this->stmt->execute($params);
    }

    public function fetch()
    {
        return $this->stmt->fetchAll();
    }
}

