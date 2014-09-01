<?php

namespace Model;

class Project {

	private $db;
	private $infos;

	public function __construct($db)
    {
        $this->db = $db;
    }

	public function load($field, $value)
	{
		$sql = 'SELECT * FROM projet WHERE '.$field.' = ?';
		$query = $this->db->prepare($sql);
		$query->execute(array($value));

		$this->infos = $this->db->fetch_one($query);

        return $this->infos;
	}

	public function get($field)
	{
		if(isset($this->infos[$field]))
			return $this->infos[$field];
		else
			return $this->all();
	}

	public function all()
	{
		return $this->infos;
	}
}