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
 */
class Roles extends BaseRepository
{
	/**
	 * Exceptions errors.
	 */
	const
		RECORD_NOT_FOUND   = 1,
		PARENT_ROLE_EXIST  = 2,
		NOT_ALLOWED_EDIT   = 3,
		NOT_ALLOWED_DELETE = 4;

	/**
	 * @var string
	 */
	private $table = ':prefix:roles';

	/**
	 * Returns all records.
	 * @return array
	 */
	public function all()
	{
		return $this->db
			->select('*')
			->from($this->table);
	}

	/**
	 * Find records.
	 * @return array
	 */
	public function findRoles()
	{
		return $this->all()
			->where('roleId in (select distinct roleId from :prefix:permissions)');
	}

	/**
	 * List of roles that are not allowed to be edited or deleted.
	 * @param array $row
	 * @return boolean
	 */
	private function notAllowed($row)
	{
		if (
			$row->name === Acl\Authorizator::ROLE_GUEST or
			$row->name === Acl\Authorizator::ROLE_MEMBER or
			$row->name === Acl\Authorizator::ROLE_ADMIN) {
			return true;
		}
	}

	/**
	 * Find record by id.
	 * @param int $id
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
	 * Find role.
	 * @param int $id
	 * @return array
	 * @throws Exception
	 */
	public function findRole($id)
	{
		$row = $this->find($id);
		if ($this->notAllowed($row)) {
			throw new Exception('The role is not allowed to be edited anyway.', self::NOT_ALLOWED_EDIT);
		}
		return $row;
	}

	/**
	 * Find record by id.
	 * @param int $id
	 * @return void
	 * @throws Exception
	 */
	public function findParent($id)
	{
		$parent = $this->all()
			->where('parent = ?', $id)
			->fetch() ? true : false;

		if ($parent) {
			throw new Exception('The record can not be deleted, you must first delete the records that are associated with it.', self::PARENT_ROLE_EXIST);
		}
		return $parent;
	}

	/**
	 * Delete record.
	 * @param int $id
	 * @return void
	 * @throws Exception
	 */
	public function delete($id)
	{
		$row = $this->find($id);
		if ($this->notAllowed($row)) {
			throw new Exception('Sorry, this role is not allowed to be deleted.', self::NOT_ALLOWED_DELETE);
		}
		$db  = $this->db->delete($this->table)->where('roleId = ?', $id)->execute();
		$this->cache->remove(Acl\Authorizator::ACL_CACHE);
		return $db;
	}

	/**
	 * Save record.
	 * @param Acl\Entity\Roles
	 * @return void
	 * @throws Exception
	 */
	public function save(Acl\Entity\Roles $entity)
	{
		if (!$entity->getId()) {
			$db = $this->db->insert($this->table, Database\Iterator::toArray($entity))->execute();
			$this->cache->remove(Acl\Authorizator::ACL_CACHE);
			return $db;

		} else {
			$db = $this->db
				->update($this->table, Database\Iterator::toArray($entity))
				->where('roleId = ?', $entity->getId())
				->execute();

			$this->cache->remove(Acl\Authorizator::ACL_CACHE);
			return $db;
		}
	}

}
