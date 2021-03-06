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
        $sql = 'SELECT id_proj, titre, desc_travail, count(id_cand) AS nb_cand FROM projet 
                LEFT JOIN candidature ON id_proj_cand=id_proj AND statut = 1
                GROUP BY id_proj';

        return $this->pdo->query($sql)->fetchAll();
    }

    public function get_project($id)
    {
        $sql = 'SELECT * FROM projet WHERE id_proj = ?';
        $query = $this->pdo->prepare($sql);
        $query->execute(array($id));

        return $this->fetch_one($query);
    }

    public function get_owned_project($id)
    {
        $sql = 'SELECT id_proj, nom, prenom FROM projet 
                LEFT JOIN utilisateur ON id_user = id_user_cre
                WHERE id_user_cre = ?';
        $query = $this->pdo->prepare($sql);
        $query->execute(array($id));

        return $this->fetch_one($query);
    }

    public function has_already_project($id)
    {
        $sql = 'SELECT id_user FROM utilisateur
                WHERE id_user = ?
                AND
                (
                id_user
                IN (SELECT id_user_cand FROM candidature WHERE statut = 1)
                OR id_user 
                IN (SELECT id_user_cre FROM projet)
                )'; // wait whaaat ? no time to explain
        $query = $this->pdo->prepare($sql);
        $query->execute(array($id));

        return $this->fetch_all($query);
    }

    public function get_places($id)
    {
        $sql = 'SELECT count(*) AS value FROM candidature
                WHERE id_proj_cand = ? AND statut = 1';
        $query = $this->pdo->prepare($sql);
        $query->execute(array($id));

        $result = $this->fetch_one($query);
        $result['percent'] = floor($result['value'] * 100 / 6); // 6 is not always the max though... fixme :)

        if($result['percent'] <= 50)
            $result['ui_color'] = 'success';
        elseif($result['percent'] > 50 AND  $result['percent'] <= 75)
            $result['ui_color'] = 'warning';
        else
            $result['ui_color'] = 'danger';

        return $result;
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
        $sql = 'SELECT * FROM utilisateur WHERE publi_info = 1 AND qualite = 1';
        $array['one'] = $this->pdo->query($sql)->fetchAll();
        $sql = 'SELECT * FROM utilisateur WHERE publi_info = 1 AND qualite = 2';
        $array['two'] = $this->pdo->query($sql)->fetchAll();

        return $array;
    }

    public function get_user($id)
    {
        $sql = 'SELECT * FROM utilisateur WHERE id_user = ?';
        $query = $this->pdo->prepare($sql);
        $query->execute(array($id));

        return $this->fetch_one($query);
    }

    public function get_user_mail($mail)
    {
        $sql = 'SELECT * FROM utilisateur WHERE mail = ?';
        $query = $this->pdo->prepare($sql);
        $query->execute(array($mail));

        return $this->fetch_one($query);
    }
    
    public function insert_user($prenom, $nom, $mail, $pwd, $year, $school, $skill, $public)
    {
        $year = ($year == '1A') ? 1 : 2;
        $public = ($public == 'on') ? 1 : 0;

        $sql = 'SELECT count(*) as count FROM utilisateur WHERE mail = ?';
        $query = $this->pdo->prepare($sql);
        $query->execute(array($mail));

        $nb_email = $this->fetch_one($query);

        if($nb_email['count'] > 0){
            return 'mail';
        }

        $sql = 'INSERT INTO utilisateur VALUES(null, 1, ?, ?, ?, ?, ?, ?, ?, ?, null)';
        $query = $this->pdo->prepare($sql);

        return $query->execute(array($prenom, $nom, $mail, $pwd, $year, $school, $skill, $public));
    }

    public function insert_project($title, $desc, $skill, $owner)
    {
        $sql = 'INSERT INTO projet VALUES(null, ?, ?, ?, CURDATE(), null, ?, null)';
        $query = $this->pdo->prepare($sql);

        return $query->execute(array($title, $desc, $skill, $owner));
    }

    public function edit_project($title, $desc, $skill, $id)
    {
        $sql = 'UPDATE projet SET titre = ?, desc_travail = ?, desc_profil = ? WHERE id_proj = ?';
        $query = $this->pdo->prepare($sql);

        return $query->execute(array($title, $desc, $skill, $id));
    }

    public function delete_project($id)
    {
        $sql = 'UPDATE candidature SET statut = 0 WHERE id_proj_cand = ?';
        $this->pdo->prepare($sql)->execute(array($id)); // outch fixme !

        $sql = 'DELETE FROM projet WHERE id_proj = ?';
        $query = $this->pdo->prepare($sql);

        return $query->execute(array($id));
    }

    public function get_applications($id)
    {
        $sql = 'SELECT * FROM candidature
                LEFT JOIN utilisateur ON id_user_cand = id_user
                WHERE id_proj_cand = ? AND statut = 0
                GROUP BY id_user_cand';
        $query = $this->pdo->prepare($sql);
        $query->execute(array($id));

        return $this->fetch_all($query);
    }

    public function accept_application($id)
    {
        $sql = 'UPDATE candidature SET statut = 1 WHERE id_cand = ?';
        $query = $this->pdo->prepare($sql);

        return $query->execute(array($id));
    }

    public function has_application($user, $project){
        $sql = 'SELECT id_cand FROM candidature
        WHERE id_user_cand = ?
        AND id_proj_cand = ?';

        $query = $this->pdo->prepare($sql);
        $query->execute(array($user, $project));

        return $this->fetch_one($query);
    }

    public function add_application($motiv, $id_user, $id_project)
    {
        if(!$this->has_application($id_user, $id_project)){
            $sql = 'INSERT INTO candidature VALUES(null, 0, ?, ?, ?)';
            $query = $this->pdo->prepare($sql);

            return $query->execute(array($motiv, $id_user, $id_project));
        }

        return false;
    }

    public function insert_idea($title, $desc, $user)
    {
        $sql = 'INSERT INTO idee VALUES(null, ?, ?, CURDATE(), ?)';
        $query = $this->pdo->prepare($sql);

        return $query->execute(array($title, $desc, $user));
    }

    public function transform_idea($idea, $user)
    {
        // TODO
    }
}