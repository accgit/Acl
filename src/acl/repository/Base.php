<?php

/**
 * Easy permissions for users.
 * Copyright (c) 2017, Zdeněk Papučík
 */
namespace Component\Acl\Repository;

use Drago;
use Nette;
use Dibi;

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

}
