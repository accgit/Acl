<?php

/**
 * Easy permissions for users.
 * Copyright (c) 2017, Zdeněk Papučík
 */
namespace Component\Acl\Repository;

use Drago, Dibi, Exception;
use Drago\Database\Iterator;
use Component\Acl\Entity;

/**
 * Roles repository.
 * @author Zdeněk Papučík
 */
class Roles extends Drago\Database\Connection
{
    	/**
	 * Database table.
	 * @var string
	 */
	private $table = ':prefix:roles';

	/**
	 * @var Entity\Roles
	 */
	private $entity;

	public function __construct(
		Dibi\Connection $db,
		Entity\Roles $entity)
	{
		parent::__construct($db);
		$this->entity = $entity;
	}

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
	 * @param  int
	 * @return array
	 */
	public function find($id)
	{
		return $this->all()
			->where('id = ?', $id)
			->fetch();
	}

	/**
	 * Find exist parent in database.
	 * @param  int
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
	 * @param  int
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
	 * @param  mixed
	 * @return void
	 */
	public function save(Entity\Roles $entity)
	{
		$entity = $this->entity;
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
