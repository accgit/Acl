<?php

/**
 * Easy permissions for users.
 * Copyright (c) 2017, Zdeněk Papučík
 */
namespace Component\Acl\Repository;

use Drago, Dibi;
use Drago\Database\Iterator;
use Component\Acl\Entity;

/**
 * Resources repository.
 * @author Zdeněk Papučík
 */
class Resources extends Drago\Database\Connection
{
    	/**
	 * Database table.
	 * @var string
	 */
	private $table = ':prefix:resources';

	/**
	 * @var Entity\Resources
	 */
	private $entity;

	public function __construct(
		Dibi\Connection $db,
		Entity\Resources $entity)
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
			->from($this->table)
			->orderBy('id asc');
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
	 * Insert or update records.
	 * @param  mixed
	 * @return void
	 */
	public function save(Entity\Resources $entity)
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
