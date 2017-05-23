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
 * Permissions repository.
 * @author Zdeněk Papučík
 */
class Permissions extends Drago\Database\Connection
{
    	/**
	 * Database table.
	 * @var string
	 */
	private $table = ':prefix:permissions';

	/**
	 * @var Entity\Permissions
	 */
	private $entity;

	public function __construct(
		Dibi\Connection $db,
		Entity\Permissions $entity)
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
			->query(''
				. 'SELECT a.id, a.allowed, r.name AS role, res.name AS resource, p.name AS privilege FROM :prefix:permissions AS a '
				. 'LEFT JOIN :prefix:resources AS res ON a.resource = res.id '
				. 'LEFT JOIN :prefix:privileges AS p ON a.privilege = p.id  '
				. 'LEFT JOIN :prefix:roles AS r ON r.id = a.role');
	}

	/**
	 * Returned record by id.
	 * @param  int
	 * @return array
	 */
	public function find($id)
	{
		return $this->db
			->select('*')
			->from($this->table)
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
	 * Insert or update record.
	 * @param  mixed
	 * @return void
	 */
	public function save(Entity\Permissions $entity)
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
