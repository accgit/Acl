<?php

/**
 * Easy permissions for users.
 * Copyright (c) 2017, Zdeněk Papučík
 */
namespace Component\Acl\Entity;
use Drago\Database;

/**
 * Roles entity.
 */
class Roles extends Database\Entity
{
	/**
	 * @var string
	 */
	public $name;

	/**
	 * @var string
	 */
	public $parent;
}
