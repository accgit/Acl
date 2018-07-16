<?php

/**
 * Easy permissions for users.
 * Copyright (c) 2017, Zdeněk Papučík
 */
namespace Component\Acl\Repository;

use Drago;
use Drago\Database\Iterator;
use Component\Acl\Entity;

/**
 * Privileges repository.
 */
class Privileges extends Drago\Database\Connection
{
	/**
	 * @return array
	 */
	public function all()
	{
		return $this->db
			->query('
				SELECT * FROM :prefix:privileges
				ORDER BY name asc');
	}

	/**
	 * @param int $id
	 * @return array
	 */
	public function find($id)
	{
		return $this->db
			->fetch('
				SELECT * FROM :prefix:privileges
				WHERE privilegeId = ?', $id);
	}

	/**
	 * @param int $id
	 */
	public function delete($id)
	{
		$this->db
			->query('
				DELETE FROM :prefix:privileges
				WHERE privilegeId = ?', $id);
	}

	public function save(Entity\Privileges $entity)
	{
		$entity->getId()?
			$this->db->query('UPDATE :prefix:privileges SET  %a', Iterator::toArray($entity), 'WHERE privilegeId = ?', $entity->getId()):
			$this->db->query('INSERT INTO :prefix:privileges %v', Iterator::toArray($entity));
	}

}
