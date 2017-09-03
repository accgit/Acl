<?php

/**
 * Easy permissions for users.
 * Copyright (c) 2017, Zdeněk Papučík
 */
namespace Component\Acl\Repository;

use Dibi;
use Drago;

/**
 * Base for repository.
 */
abstract class BaseRepository extends Drago\Database\Connection
{
	/**
	 * @var Drago\Caching\Caches
	 */
	public $caches;

	public function __construct(
		Dibi\Connection $db,
		Drago\Caching\Caches $caches)
	{
		parent::__construct($db);
		$this->caches = $caches;
	}

}
