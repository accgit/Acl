<?php

/**
 * Easy permissions for users.
 * Copyright (c) 2017, Zdeněk Papučík
 */
namespace Component\Acl\Entity;
use Drago\Database;

/**
 * Resources entity.
 * @author Zdeněk Papučík
 */
class Resources extends Database\Entity
{
	/**
	 * @var string
	 */
	public $name;
}
