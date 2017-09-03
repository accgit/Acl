<?php

/**
 * Easy permissions for users.
 * Copyright (c) 2017, Zdeněk Papučík
 */
namespace Component\Acl\Entity;
use Drago\Database;

/**
 * Permissions entity.
 */
class Permissions extends Database\Entity
{
	/**
	 * @var int
	 */
	public $roleId;

	/**
	 * @var int
	 */
	public $resourceId;

	/**
	 * @var int
	 */
	public $privilegeId;

	/**
	 * @var string
	 */
	public $allowed;
}
