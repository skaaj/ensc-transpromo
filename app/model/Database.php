<?php

namespace Model;

class Database {

	protected $pdo;

    public function __construct($host, $database, $user, $password)
    {
        try {
            $this->pdo = new \PDO(
                'mysql:dbname='.$database.';host='.$host, $user,$password
            );
            $this->pdo->exec('SET CHARSET UTF8');
        } catch (\PDOException $exception) {
            die('Impossible de se connecter au serveur MySQL');
        }
    }

    protected function load($db)
    {
        $this->pdo = $db->pdo;
    }

    public function fetch_one(\PDOStatement $query)
    {
        if ($query->rowCount() != 1) {
            return false;
        } else {
            return $query->fetch();
        }
    }

    public function fetch_all(\PDOStatement $query)
    {
        return $query->fetchAll();
    }

    public function prepare($sql){
        return $this->pdo->prepare($sql);
    }
}