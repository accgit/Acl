<?php

/**
 * Easy permissions for users.
 * Copyright (c) 2017, Zdeněk Papučík
 */
namespace Component\Acl\Repository;

use Exception;
use Drago\Database;
use Component\Acl;

/**
 * Roles repository.
 * @author Zdeněk Papučík
 */
class Roles extends BaseRepository
{
	// Exceptions errors.
	const
		RECORD_NOT_FOUND   = 1,
		PARENT_ROLE_EXIST  = 2,
		NOT_ALLOWED_DELETE = 3,
		INVALID_ROLE_NAME  = 4;

	/**
	 * Database table.
	 * @var string
	 */
	private $table = ':prefix:roles';

	/**
	 * Returned all records.
	 * @return array
	 */
	public function all()
	{
		return $this->db
			->select('*')
			->from($this->table);
	}

	/**
	 * Returned record by id.
	 * @param int
	 * @return void
	 * @throws Exception
	 */
	public function find($id)
	{
		$row = $this->all()
			->where('roleId = ?', $id)
			->fetch();

		if (!$row) {
			throw new Exception('Sorry, but the record was not found.', self::RECORD_NOT_FOUND);
		}
		return $row;
	}

	/**
	 * Find inherited role parent.
	 * @param int
	 * @return void
	 * @throws Exception
	 */
	public function findParent($id)
	{
		$parent = $this->all()
			->where('parent = ?', $id)
			->fetch() ? TRUE : FALSE;

		if ($parent) {
			throw new Exception('The record can not be deleted, you must first delete the records that are associated with it.', self::PARENT_ROLE_EXIST);
		}
		return $parent;
	}

	/**
	 * Delete record.
	 * @param int
	 * @return void
	 * @throws Exception
	 */
	public function delete($id)
	{
		$row = $this->find($id);
		if ($row->name === Acl\Authorizator::ROLE_GUEST or $row->name === Acl\Authorizator::ROLE_MEMBER or $row->name === Acl\Authorizator::ROLE_ADMIN) {
			throw new Exception('Sorry, this role is not allowed to be deleted.', self::NOT_ALLOWED_DELETE);
		}
		$db  = $this->db->delete($this->table)->where('roleId = ?', $id)->execute();
		$this->caches->removeCache(Acl\Authorizator::ACL_CACHE);
		return $db;
	}

	/**
	 * Insert or update record.
	 * @param Acl\Entity\Roles
	 * @return void
	 * @throws Exception
	 */
	public function save(Acl\Entity\Roles $entity)
	{
		if (!$entity->getId()) {
			if ($entity->name === Acl\Authorizator::ROLE_ADMIN) {
				throw new Exception('Invalid role name.', self::INVALID_ROLE_NAME);
			}
			$db = $this->db->insert($this->table, Database\Iterator::set($entity))->execute();
			$this->caches->removeCache(Acl\Authorizator::ACL_CACHE);
			return $db;

		} else {
			$db = $this->db
				->update($this->table, Database\Iterator::set($entity))
				->where('roleId = ?', $entity->getId())
				->execute();

			$this->caches->removeCache(Acl\Authorizator::ACL_CACHE);
			return $db;
		}
	}

}
