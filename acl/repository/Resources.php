<?php

/**
 * Easy permissions for users.
 * Copyright (c) 2017, Zdeněk Papučík
 */
namespace Component\Acl\Repository;

use Drago;
use Drago\Database\Iterator;

use Component\Acl;

/**
 * Resources repository.
 * @author Zdeněk Papučík
 */
class Resources extends Drago\Database\Connection
{
    	// Exceptions errors.
	const
		ROLE_NOT_FOUND = 1;

	/**
	 * Database table.
	 * @var string
	 */
	private $table = ':prefix:resources';

	/**
	 * Returned all records.
	 * @return array
	 */
	public function all()
	{
		return $this->db
			->select('*')
			->from($this->table)
			->orderBy('id asc');
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
			throw new Exception('Sorry, but the record was not found.', self::ROLE_NOT_FOUND);
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
			->where('id = ?', $id)
			->execute();
	}

	/**
	 * Insert or update records.
	 * @param mixed
	 * @return void
	 */
	public function save(Acl\Entity\Resources $entity)
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
