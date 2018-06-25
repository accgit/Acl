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
 * Permissions repository.
 */
class Permissions extends BaseRepository
{
	/**
	 * Exceptions errors.
	 */
	const
		RECORD_NOT_FOUND = 1,
		DUPLICATION_RULE = 2;

	/**
	 * @return array
	 */
	public function items()
	{
		return $this->db
			->query('SELECT * FROM :prefix:permissions');
	}

	/**
	 * @return array
	 */
	public function all()
	{
		return $this->db
			->query('
				SELECT a.id, a.allowed, r.name AS role, res.name AS resource, p.name AS privilege FROM :prefix:permissions AS a
				JOIN :prefix:resources AS res using (resourceId)
				JOIN :prefix:privileges AS p using (privilegeId)
				JOIN :prefix:roles AS r using (roleId)');
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
				SELECT * FROM :prefix:permissions
				WHERE id = ?', $id);
		if (!$row) {
			throw new Exception('Sorry, but the record was not found.', self::RECORD_NOT_FOUND);
		}
		return $row;
	}

	/**
	 * @return array
	 */
	public function rules()
	{
		return $this->db
			->query('
				SELECT * FROM :prefix:permissions
				GROUP BY allowed, roleId');
	}

	/**
	 * @return array
	 */
	public function resources()
	{
		return $this->db
			->query('
				SELECT a.allowed, r.name AS role, res.name AS resource FROM :prefix:permissions AS a
				LEFT JOIN :prefix:resources AS res using (resourceId)
				LEFT JOIN :prefix:privileges AS p using (privilegeId)
				LEFT JOIN :prefix:roles AS r using (roleId)
				GROUP BY a.allowed, r.name, res.name');
	}

	/**
	 * @return array
	 */
	public function privileges()
	{
		return $this->db
			->query('
				SELECT a.id, a.allowed, r.name AS role, res.name AS resource, p.name as privilege FROM :prefix:permissions AS a
				LEFT JOIN :prefix:resources AS res using (resourceId)
				LEFT JOIN :prefix:privileges AS p using (privilegeId)
				LEFT JOIN :prefix:roles AS r using (roleId)
				GROUP BY a.allowed, r.name, res.name, p.name');
	}

	/**
	 * @param int $id
	 */
	public function delete($id)
	{
		$this->db
			->query('
				DELETE FROM :prefix:permissions
				WHERE id = ?', $id);
		$this->removeCache();
	}

	public function save(Entity\Permissions $entity)
	{
		!$entity->getId() ?
			$this->db->query('INSERT INTO :prefix:permissions %v', Iterator::toArray($entity)) :
			$this->db->query('UPDATE :prefix:permissions SET %a',  Iterator::toArray($entity), 'WHERE id = ?', $entity->getId());
		$this->removeCache();
	}

}
