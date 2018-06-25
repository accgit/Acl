<?php

/**
 * Easy permissions for users.
 * Copyright (c) 2017, Zdeněk Papučík
 */
namespace Component\Acl\Repository;

use Drago;
use Exception;
use Drago\Database\Iterator;
use Component\Acl\Entity;

/**
 * Privileges repository.
 */
class Privileges extends Drago\Database\Connection
{
	/**
	 * Exceptions errors.
	 */
	const RECORD_NOT_FOUND = 1;

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
	 * @throws Exception
	 */
	public function find($id)
	{
		$row = $this->db
			->fetch('
				SELECT * FROM :prefix:privileges
				WHERE privilegeId = ?', $id, '
				ORDER BY name asc');
		if (!$row) {
			throw new Exception('Sorry, but the record was not found.', self::RECORD_NOT_FOUND);
		}
		return $row;
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
		!$entity->getId() ?
			$this->db->query('INSERT INTO :prefix:privileges %v', Iterator::toArray($entity)) :
			$this->db->query('UPDATE :prefix:privileges SET %a',  Iterator::toArray($entity), 'WHERE privilegeId = ?', $entity->getId());
	}

}
