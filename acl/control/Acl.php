<?php

/**
 * Easy permissions for users.
 * Copyright (c) 2017, Zdeněk Papučík
 */

namespace Component;

use Component\Acl\Control;
use Nette\Application\UI;

/**
 * Dynamic processing of user roles.
 * @author Zdeněk Papučík
 */
class Acl extends UI\Control
{
	/**
	 * @var Control\Roles
	 */
	private $roles;

	/**
	 * @var Control\Resources
	 */
	private $resources;

	/**
	 * @var Control\Privileges
	 */
	private $privileges;

	/**
	 * @var Control\Permissions
	 */
	private $permissions;

	public function __construct(
		Control\Roles $roles,
		Control\Resources $resources,
		Control\Privileges $privileges,
		Control\Permissions $permissions)
	{
		parent::__construct();
		$this->roles = $roles;
		$this->resources   = $resources;
		$this->privileges  = $privileges;
		$this->permissions = $permissions;
	}

	/**
	 * @return Control\Roles
	 */
	public function createComponentRoles()
	{
		return $this->roles;
	}

	/**
	 * @return Control\Resources
	 */
	public function createComponentResources()
	{
		return $this->resources;
	}

	/**
	 * @return Control\Privileges
	 */
	public function createComponentPrivileges()
	{
		return $this->privileges;
	}

	/**
	 * @return Control\Permissions
	 */
	public function createComponentPermissions()
	{
		return $this->permissions;
	}

}
