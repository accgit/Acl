<?php

/**
 * Easy permissions for users.
 * Copyright (c) 2017, Zdeněk Papučík
 */
namespace Component\Acl\Entity;
use Drago\Database;

/**
 * Privileges entity.
 */
class Privileges extends Database\Entity
{
	/**
	 * @var string
	 */
	public $name;
}
