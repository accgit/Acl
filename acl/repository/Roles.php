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
		ROLE_NOT_FOUND = 1;

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
		$row = $this->all()->where('id = ?', $id)->fetch();
		if (!$row) {
			throw new Exception('Sorry, but the record was not found.', self::ROLE_NOT_FOUND);
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
			throw new Exception('The record can not be deleted, you must first delete the records that are associated with it.', 1);
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
		if ($row->name === Acl\Authorizator::ROLE_GUEST) {

		}
	}

	/**
	 * Insert or update record.
	 * @param mixed
	 * @return void
	 */
	public function save(Acl\Entity\Roles $entity)
	{
		if (!$entity->getId()) {
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
