<?php

/**
 * Easy permissions for users.
 * Copyright (c) 2017, Zdeněk Papučík
 */
namespace Component\Acl\Repository;

use Exception;
use Drago\Database\Iterator;
use Component\Acl\Entity;

/**
 * Resources repository.
 */
class Resources extends BaseRepository
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
				SELECT * FROM :prefix:resources
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
				SELECT * FROM :prefix:resources
				WHERE resourceId = ?', $id, '
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
				DELETE FROM :prefix:resources
				WHERE resourceId = ?', $id);
		$this->removeCache();
	}

	public function save(Entity\Resources $entity)
	{
		!$entity->getId() ?
			$this->db->query('INSERT INTO :prefix:resources %v', Iterator::toArray($entity)) :
			$this->db->query('UPDATE :prefix:resources SET %a',  Iterator::toArray($entity), 'WHERE resourceId = ?', $entity->getId());
		$this->removeCache();
	}

}
