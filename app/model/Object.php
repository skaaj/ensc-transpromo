<?php

namespace Model;

class Object extends Database {

	public function __construct($db)
    {
        parent::load($db);
    }

	public function list_table()
	{
		$query = $this->pdo->query('SELECT * FROM test');

		return $query->fetchAll();
	}
}