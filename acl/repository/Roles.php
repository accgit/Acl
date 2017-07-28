<?php

/**
 * Easy permissions for users.
 * Copyright (c) 2017, Zdeněk Papučík
 */
namespace Component\Acl\Repository;

use Drago, Exception;
use Drago\Database\Iterator;

use Component\Acl;

/**
 * Roles repository.
 * @author Zdeněk Papučík
 */
class Roles extends Drago\Database\Connection
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
	 * @return array
	 */
	public function find($id)
	{
		$row = $this->all()
			->where('id = ?', $id)
			->fetch();

		if (!$row) {
			throw new Exception('Sorry, but the record was not found.', self::RECORD_NOT_FOUND);
		}
		return $row;
	}

	/**
	 * Find exist parent in database.
	 * @param int
	 * @return bool
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
	 */
	public function delete($id)
	{
		$row = $this->find($id);
		if ($row->name === Acl\Authorizator::ROLE_GUEST or $row->name === Acl\Authorizator::ROLE_MEMBER or $row->name === Acl\Authorizator::ROLE_ADMIN) {
			throw new Exception('Sorry, this role is not allowed to be deleted.', self::NOT_ALLOWED_DELETE);
		}
		return $this->db
			->delete($this->table)
			->where('id = ?', $id)
			->execute();
	}

	/**
	 * Insert or update record.
	 * @param mixed
	 * @return void
	 */
	public function save(Acl\Entity\Roles $entity)
	{
		if (!$entity->getId()) {
			if ($entity->name === Acl\Authorizator::ROLE_ADMIN) {
				throw new Exception('Invalid role name.', self::INVALID_ROLE_NAME);
			}
			return $this->db
				->insert($this->table, Iterator::set($entity))
				->execute();
		} else {
			return $this->db
				->update($this->table, Iterator::set($entity))
				->where('id = ?', $entity->getId())
				->execute();
		}
	}

}
