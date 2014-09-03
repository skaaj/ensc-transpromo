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

    public function get_informations()
    {
        $sql = 'SELECT * FROM nouvelle';
        return $this->pdo->query($sql)->fetchAll();
    }

    public function get_deadlines()
    {
        $sql = 'SELECT * FROM date_but';
        return $this->pdo->query($sql)->fetchAll();
    }

    public function get_projects()
    {
        $sql = 'SELECT id_proj, titre, desc_travail FROM projet';
        return $this->pdo->query($sql)->fetchAll();
    }

    public function get_project($id)
    {
        $sql = 'SELECT * FROM projet WHERE id_proj = ?';
        $query = $this->pdo->prepare($sql);
        $query->execute(array($id));

        return $this->fetch_one($query);
    }

    public function get_ideas()
    {
        $sql = 'SELECT id_idee, titre, resume FROM idee';
        return $this->pdo->query($sql)->fetchAll();
    }

    public function get_idea($id)
    {
        $sql = 'SELECT * FROM idee WHERE id_idee = ?';
        $query = $this->pdo->prepare($sql);
        $query->execute(array($id));

        return $this->fetch_one($query);
    }

    public function get_members()
    {
        $sql = 'SELECT * FROM utilisateur WHERE publi_info = 1';
        return $this->pdo->query($sql)->fetchAll();
    }
}