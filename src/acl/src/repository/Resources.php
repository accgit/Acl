<?php

/**
 * Easy permissions for users.
 * Copyright (c) 2017, Zdeněk Papučík
 */
namespace Component\Acl\Repository;

use Drago\Database\Iterator;
use Component\Acl\Entity;

/**
 * Resources repository.
 */
class Resources extends BaseRepository
{
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
	 */
	public function find($id)
	{
		return $this->db
			->fetch('
				SELECT * FROM :prefix:resources
				WHERE resourceId = ?', $id);
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
		$entity->getId() ?
		$this->db->query('UPDATE :prefix:resources SET  %a', Iterator::toArray($entity), 'WHERE resourceId = ?', $entity->getId()) :
		$this->db->query('INSERT INTO :prefix:resources %v', Iterator::toArray($entity));
		$this->removeCache();
	}

}
