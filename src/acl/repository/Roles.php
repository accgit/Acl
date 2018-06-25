<?php

/**
 * Easy permissions for users.
 * Copyright (c) 2017, Zdeněk Papučík
 */
namespace Component\Acl\Repository;

use Exception;
use Drago\Database\Iterator;

use Component\Acl;
use Component\Acl\Entity;

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
	 * @return array
	 */
	public function all()
	{
		return $this->db
			->fetchAll('SELECT * FROM :prefix:roles');
	}

	/**
	 * @return array
	 */
	public function findRoles()
	{
		return $this->db
			->query('
				SELECT * FROM :prefix:roles
				WHERE roleId IN (SELECT DISTINCT roleId FROM :prefix:permissions)');
	}

	/**
	 * @param array $row
	 * @return bool
	 */
	private function isAllowed($row)
	{
		if (
			$row->name === Acl\Authorizator::ROLE_GUEST or
			$row->name === Acl\Authorizator::ROLE_MEMBER or
			$row->name === Acl\Authorizator::ROLE_ADMIN) {
			return true;
		}
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
				SELECT * FROM :prefix:roles
				WHERE roleId = ?', $id);
		if (!$row) {
			throw new Exception('Sorry, but the record was not found.', self::RECORD_NOT_FOUND);

		}
		return $row;
	}

	/**
	 * @param int $id
	 * @return array
	 * @throws Exception
	 */
	public function findRole($id)
	{
		$row = $this->db
			->fetch('
				SELECT * FROM :prefix:roles
				WHERE roleId = ?', $id);

		if ($this->isAllowed($row)) {
			throw new Exception('The role is not allowed to be edited anyway.', self::NOT_ALLOWED_EDIT);
		}
		return $row;
	}

	/**
	 * @param int $id
	 * @return array
	 * @throws Exception
	 */
	public function findParent($id)
	{
		$row = $this->db
			->fetch('
				SELECT * FROM :prefix:roles
				WHERE parent = ?', $id) ? true : false;
		if ($row) {
			throw new Exception('The record can not be deleted, you must first delete the records that are associated with it.', self::PARENT_ROLE_EXIST);
		}
		return $row;
	}

	/**
	 * @param int $id
	 * @throws Exception
	 */
	public function delete($id)
	{
		$row = $this->db
			->fetch('
				SELECT * FROM :prefix:roles
				WHERE roleId = ?', $id);

		if ($this->isAllowed($row)) {
			throw new Exception('Sorry, this role is not allowed to be deleted.', self::NOT_ALLOWED_DELETE);
		}
		$this->db
			->query('
				DELETE FROM :prefix:roles
				WHERE roleId = ?', $id);
		$this->removeCache();
	}

	public function save(Entity\Roles $entity)
	{
		!$entity->getId() ?
			$this->db->query('INSERT INTO :prefix:roles %v', Iterator::toArray($entity)) :
			$this->db->query('UPDATE :prefix:roles SET %a',  Iterator::toArray($entity), 'WHERE roleId = ?', $entity->getId());
		$this->removeCache();
	}

}
