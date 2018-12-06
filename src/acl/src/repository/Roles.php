<?php

/**
 * Easy permissions for users.
 * Copyright (c) 2017, Zdeněk Papučík
 */
namespace Component\Acl\Repository;

use Drago;
use Exception;
use Drago\Database\Iterator;

use Component\Acl;
use Component\Acl\Entity;

/**
 * Roles repository.
 */
class Roles extends Drago\Database\Connection
{
	/**
	 * Exceptions errors.
	 */
	const
		PARENT_EXIST = 2,
		NOT_ALLOWED  = 3;

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
	 * @throws Exception
	 */
	public function isAllowed($row)
	{
		if ($row->name === Acl\Authorizator::ROLE_GUEST or $row->name === Acl\Authorizator::ROLE_MEMBER or $row->name === Acl\Authorizator::ROLE_ADMIN) {
			throw new Exception('The role is not allowed to be edited or deleted.', self::NOT_ALLOWED);
		}
		return true;
	}

	/**
	 * @param int $id
	 * @return array
	 */
	public function find($id)
	{
		return $this->db
			->fetch('
				SELECT * FROM :prefix:roles
				WHERE roleId = ?', $id);
	}

	/**
	 * @param int $id
	 * @return bool
	 * @throws Exception
	 */
	public function findParent($id)
	{
		$row = $this->db
			->fetch('
				SELECT * FROM :prefix:roles
				WHERE parent = ?', $id) ? true : false;
		if ($row) {
			throw new Exception('The record can not be deleted, you must first delete the records that are associated with it.', self::PARENT_EXIST);
		}
		return $row;
	}

	/**
	 * @param int $id
	 */
	public function delete($id)
	{
		$this->db
			->query('
				DELETE FROM :prefix:roles
				WHERE roleId = ?', $id);
	}

	public function save(Entity\Roles $entity)
	{
		$entity->getId() ?
		$this->db->query('UPDATE :prefix:roles SET  %a', Iterator::toArray($entity), 'WHERE roleId = ?', $entity->getId()) :
		$this->db->query('INSERT INTO :prefix:roles %v', Iterator::toArray($entity));
	}

}
