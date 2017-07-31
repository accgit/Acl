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
 * Privileges repository.
 * @author Zdeněk Papučík
 */
class Privileges extends Drago\Database\Connection
{
        // Exceptions errors.
	const
		RECORD_NOT_FOUND = 1;

	/**
	 * Database table.
	 * @var string
	 */
	private $table = ':prefix:privileges';

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
			->where('privilegeId = ?', $id)
			->fetch();

		if (!$row) {
			throw new Exception('Sorry, but the record was not found.', self::RECORD_NOT_FOUND);
		}
		return $row;
	}

	/**
	 * Delete record.
	 * @param int
	 * @return void
	 */
	public function delete($id)
	{
		return $this->db
			->delete($this->table)
			->where('privilegeId = ?', $id)
			->execute();
	}

	/**
	 * Insert or update record.
	 * @param mixed
	 * @return void
	 */
	public function save(Acl\Entity\Privileges $entity)
	{
		if (!$entity->getId()) {
			return $this->db
				->insert($this->table, Iterator::set($entity))
				->execute();
		} else {
			return $this->db
				->update($this->table, Iterator::set($entity))
				->where('privilegeId = ?', $entity->getId())
				->execute();
		}
	}

}
