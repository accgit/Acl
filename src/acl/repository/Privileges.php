<?php

/**
 * Easy permissions for users.
 * Copyright (c) 2017, Zdeněk Papučík
 */
namespace Component\Acl\Repository;

use Drago;
use Exception;
use Drago\Database;
use Component\Acl;

/**
 * Privileges repository.
 */
class Privileges extends Drago\Database\Connection
{
	/**
	 * Exceptions errors.
	 */
	const RECORD_NOT_FOUND = 1;

	/**
	 * @var string
	 */
	private $table = ':prefix:privileges';

	/**
	 * Returns all records.
	 * @return array
	 */
	public function all()
	{
		return $this->db
			->select('*')
			->from($this->table)
			->orderBy('name asc');
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
			->where('privilegeId = ?', $id)
			->fetch();

		if (!$row) {
			throw new Exception('Sorry, but the record was not found.', self::RECORD_NOT_FOUND);
		}
		return $row;
	}

	/**
	 * Delete record.
	 * @param int $id
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
	 * Save record.
	 * @param Acl\Entity\Privileges
	 * @return void
	 */
	public function save(Acl\Entity\Privileges $entity)
	{
		if (!$entity->getId()) {
			return $this->db
				->insert($this->table, Database\Iterator::toArray($entity))
				->execute();
		} else {
			return $this->db
				->update($this->table, Database\Iterator::toArray($entity))
				->where('privilegeId = ?', $entity->getId())
				->execute();
		}
	}

}
