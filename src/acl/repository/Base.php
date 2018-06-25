<?php

/**
 * Easy permissions for users.
 * Copyright (c) 2017, Zdeněk Papučík
 */
namespace Component\Acl\Repository;

use Drago;
use Nette;
use Dibi;
use Component;

/**
 * Base for repository.
 */
abstract class BaseRepository extends Drago\Database\Connection
{
	/**
	 * @var Nette\Caching\Cache
	 */
	public $cache;

	public function __construct(Nette\Caching\Cache $cache, Dibi\Connection $db)
	{
		parent::__construct($db);
		$this->cache = $cache;
	}

	/**
	 * @return void
	 */
	public function removeCache()
	{
		return $this->cache->remove(Component\Acl\Authorizator::ACL_CACHE);
	}

}
