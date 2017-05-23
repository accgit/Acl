<?php

/**
 * Easy permissions for users.
 * Copyright (c) 2017, Zdeněk Papučík
 */
namespace Component\Acl\Entity;
use Drago\Database;

/**
 * Permissions entity.
 * @author Zdeněk Papučík
 */
class Permissions extends Database\Entity
{
	/**
	 * @var string
	 */
	public $role;

	/**
	 * @var string
	 */
	public $resource;

	/**
	 * @var string
	 */
	public $privilege;

	/**
	 * @var string
	 */
	public $allowed;
}
