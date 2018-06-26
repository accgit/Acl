<?php

/**
 * Easy permissions for users.
 * Copyright (c) 2017, Zdeněk Papučík
 */
namespace Component;
use Component\Acl\Control;

/**
 * Dynamic processing of user roles.
 */
trait Acl
{
	/**
	 * @var Control\Roles
	 */
	protected $roles;

	/**
	 * @var Control\Resources
	 */
	protected $resources;

	/**
	 * @var Control\Privileges
	 */
	protected $privileges;

	/**
	 * @var Control\Permissions
	 */
	protected $permissions;

	public function injectAclComponents(
		Control\Roles $roles,
		Control\Resources $resources,
		Control\Privileges $privileges,
		Control\Permissions $permissions)
	{
		$this->roles = $roles;
		$this->resources = $resources;
		$this->privileges = $privileges;
		$this->permissions = $permissions;
	}

	/**
	 * @return Control\Roles
	 */
	protected function createComponentAclRoles()
	{
		return $this->roles;
	}

	/**
	 * @return Control\Resources
	 */
	protected function createComponentAclResources()
	{
		return $this->resources;
	}

	/**
	 * @return Control\Privileges
	 */
	protected function createComponentAclPrivileges()
	{
		return $this->privileges;
	}

	/**
	 * @return Control\Permissions
	 */
	protected function createComponentAclPermissions()
	{
		return $this->permissions;
	}

	public function handleAcl()
	{
		if ($this->isAjax()) {
			$this->redrawControl('acl');
		}
	}

}
