<?php

/**
 * Easy permissions for users.
 * Copyright (c) 2017, Zdeněk Papučík
 */
namespace Component\Acl\Repository;

use Exception;
use Drago\Database\Iterator;
use Component\Acl;

/**
 * Permissions repository.
 */
class Permissions extends BaseRepository
{
	// Exceptions errors.
	const

		RECORD_NOT_FOUND = 1,
		DUPLICATION_RULE = 2;

	/**
	 * @var string
	 */
	private $table = ':prefix:permissions';

	/**
	 * Returns all records.
	 * @return array
	 */
	public function items()
	{
		return $this->db
			->select('*')
			->from($this->table);
	}

	/**
	 * Returns all from joined tables.
	 * @return array
	 */
	public function all()
	{
		return $this->db
			->query('
				SELECT a.id, a.allowed, r.name AS role, res.name AS resource, p.name AS privilege
				FROM :prefix:permissions AS a JOIN :prefix:resources AS res using (resourceId)
				JOIN :prefix:privileges AS p using (privilegeId)
				JOIN :prefix:roles AS r using (roleId)');
	}

	/**
	 * Find record by id.
	 * @param int
	 * @return void
	 * @throws Exception
	 */
	public function find($id)
	{
		$row = $this->items()
			->where('id = ?', $id)
			->fetch();

		if (!$row) {
			throw new Exception('Sorry, but the record was not found.', self::RECORD_NOT_FOUND);
		}
		return $row;
	}

	/**
	 * Group by rules.
	 * @return array
	 */
	public function rules()
	{
		return $this->items()
			->groupBy('allowed, roleId');
	}

	/**
	 * Group by resources.
	 * @return array
	 */
	public function resources()
	{
		return $this->db->query('
			SELECT a.allowed, r.name AS role, res.name AS resource FROM :prefix:permissions AS a
			JOIN :prefix:resources AS res using (resourceId) JOIN :prefix:privileges AS p using (privilegeId)
			JOIN :prefix:roles AS r using (roleId) GROUP BY a.allowed, r.name, res.name');
	}

	/**
	 * Group by privileges.
	 * @return array
	 */
	public function privileges()
	{
		return $this->db->query('
			SELECT a.id, a.allowed, r.name AS role, res.name AS resource, p.name as privilege FROM :prefix:permissions AS a
			JOIN :prefix:resources AS res using (resourceId) JOIN :prefix:privileges AS p using (privilegeId)
			JOIN :prefix:roles AS r using (roleId) GROUP BY a.allowed, r.name, res.name, p.name');
	}

	/**
	 * Delete record.
	 * @param int
	 * @return void
	 */
	public function delete($id)
	{
		$row = $this->db->delete($this->table)->where('id = ?', $id)->execute();
		$this->cache->remove(Acl\Authorizator::ACL_CACHE);
		return $row;
	}

	/**
	 * Save record.
	 * @param Acl\Entity\Permissions
	 * @return void
	 */
	public function save(Acl\Entity\Permissions $entity)
	{
		if (!$entity->getId()) {
			$row = $this->db->insert($this->table, Iterator::toArray($entity))->execute();
			$this->cache->remove(Acl\Authorizator::ACL_CACHE);
			return $row;
		} else {
			$row = $this->db
				->update($this->table, Iterator::toArray($entity))
				->where('id = ?', $entity->getId())
				->execute();

			$this->cache->remove(Acl\Authorizator::ACL_CACHE);
			return $row;
		}
	}

}
