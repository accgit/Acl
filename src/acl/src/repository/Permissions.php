<?php

/**
 * Easy permissions for users.
 * Copyright (c) 2017, Zdeněk Papučík
 */
namespace Component\Acl\Repository;

use Dibi;
use Nette;
use Drago;
use Drago\Database\Iterator;
use Component\Acl\Entity;
use Component\Acl\Authorizator;

/**
 * Permissions repository.
 */
class Permissions extends Drago\Database\Connection
{
    	/**
	 * @var Nette\Caching\Cache
	 */
	public $cache;

	public function __construct(
		Nette\Caching\Cache $cache,
		Dibi\Connection $db)
	{
		parent::__construct($db);
		$this->cache = $cache;
	}

	/**
	 * @return void
	 */
	private function removeCache()
	{
		return $this->cache->remove(Authorizator::ACL_CACHE);
	}

	/**
	 * @return array
	 */
	public function items()
	{
		return $this->db
			->query('SELECT * FROM :prefix:permissions');
	}

	/**
	 * @return array
	 */
	public function all()
	{
		return $this->db
			->query('
				SELECT a.id, a.allowed,
				s.name AS resource,
				p.name AS privilege,
				r.name AS role

				FROM :prefix:permissions AS a
				JOIN :prefix:resources   AS s USING (resourceId)
				JOIN :prefix:privileges  AS p USING (privilegeId)
				JOIN :prefix:roles       AS r USING (roleId)');
	}

	/**
	 * @param int $id
	 * @return array
	 */
	public function find($id)
	{
		return $this->db
			->fetch('
				SELECT * FROM :prefix:permissions
				WHERE id = ?', $id);
	}

	/**
	 * @return array
	 */
	public function rules()
	{
		return $this->db
			->query('
				SELECT * FROM :prefix:permissions
				GROUP BY allowed, roleId');
	}

	/**
	 * @return array
	 */
	public function resources()
	{
		return $this->db
			->query('
				SELECT a.allowed,
				s.name AS resource,
				r.name AS role

				FROM :prefix:permissions     AS a
				LEFT JOIN :prefix:resources  AS s USING (resourceId)
				LEFT JOIN :prefix:privileges AS p USING (privilegeId)
				LEFT JOIN :prefix:roles      AS r USING (roleId)
				GROUP BY a.allowed, r.name, s.name');
	}

	/**
	 * @return array
	 */
	public function privileges()
	{
		return $this->db
			->query('
				SELECT a.id, a.allowed,
				s.name AS resource,
				p.name AS privilege,
				r.name AS role

				FROM :prefix:permissions     AS a
				LEFT JOIN :prefix:resources  AS s USING (resourceId)
				LEFT JOIN :prefix:privileges AS p USING (privilegeId)
				LEFT JOIN :prefix:roles      AS r USING (roleId)
				GROUP BY a.allowed, r.name, s.name, p.name');
	}

	/**
	 * @param int $id
	 */
	public function delete($id)
	{
		$this->db
			->query('
				DELETE FROM :prefix:permissions
				WHERE id = ?', $id);
				$this->removeCache();
	}

	public function save(Entity\Permissions $entity)
	{
		$entity->getId() ?
		$this->db->query('UPDATE :prefix:permissions SET  %a', Iterator::toArray($entity), 'WHERE id = ?', $entity->getId()) :
		$this->db->query('INSERT INTO :prefix:permissions %v', Iterator::toArray($entity));
		$this->removeCache();
	}

}
