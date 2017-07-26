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
 * Privileges repository.
 * @author Zdeněk Papučík
 */
class Privileges extends Drago\Database\Connection
{
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
		return $this->all()
			->where('id = ?', $id)
			->fetch();
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
				->where('id = ?', $entity->getId())
				->execute();
		}
	}

}
