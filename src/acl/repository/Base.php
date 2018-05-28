<?php

/**
 * Easy permissions for users.
 * Copyright (c) 2017, Zdeněk Papučík
 */
namespace Component\Acl\Repository;

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

	public function __construct(
		Dibi\Connection $db,
		Nette\Caching\Cache $cache)
	{
		parent::__construct($db);
		$this->cache = $cache;
	}

}
