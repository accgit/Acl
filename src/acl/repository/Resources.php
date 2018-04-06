<?php

/**
 * Easy permissions for users.
 * Copyright (c) 2017, Zdeněk Papučík
 */
namespace Component\Acl\Repository;

use Exception;
use Drago\Database;
use Component\Acl;

/**
 * Resources repository.
 */
class Resources extends BaseRepository
{
	// Exceptions errors.
	const RECORD_NOT_FOUND = 1;

	/**
	 * @var string
	 */
	private $table = ':prefix:resources';

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
	 * @param int
	 * @return void
	 * @throws Exception
	 */
	public function find($id)
	{
		$row = $this->all()
			->where('resourceId = ?', $id)
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
		$row = $this->db->delete($this->table)->where('resourceId = ?', $id)->execute();
		$this->caches->removeCache(Acl\Authorizator::ACL_CACHE);
		return $row;
	}

	/**
	 * Save record.
	 * @param Acl\Entity\Resources
	 * @return void
	 */
	public function save(Acl\Entity\Resources $entity)
	{
		if (!$entity->getId()) {
			$row = $this->db->insert($this->table, Database\Iterator::toArray($entity))->execute();
			$this->caches->removeCache(Acl\Authorizator::ACL_CACHE);
			return $row;
		} else {
			$row = $this->db
				->update($this->table, Database\Iterator::toArray($entity))
				->where('resourceId = ?', $entity->getId())
				->execute();

			$this->caches->removeCache(Acl\Authorizator::ACL_CACHE);
			return $row;
		}
	}

}
