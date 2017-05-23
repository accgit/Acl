<?php

/**
 * Easy permissions for users.
 * Copyright (c) 2017, Zdeněk Papučík
 */
namespace Component\Acl;

use Nette\Security;
use Component\Acl\Repository;
use Drago\Caching\Caches;

/**
 * Managing User Permissions.
 * @author Zdeněk Papučík
 */
class Permission
{
	// System administrator.
	const SystemRole = 'root';

	/**
	 * Key for cache.
	 * @var string
	 */
	private $key = 'cache.permissions';

	/**
	 * @var Repository\Roles;
	 */
	private $roles;

	/**
	 * @var Repository\Resources
	 */
	private $resources;

	/**
	 * @var Repository\Permissions
	 */
	private $permissions;

	/**
	 * @var Caches
	 */
	private $caches;

	public function __construct(
		Caches $caches,
		Repository\Roles $roles,
		Repository\Resources $resources,
		Repository\Permissions $permissions)
	{
		$this->caches = $caches;
		$this->roles  = $roles;
		$this->permissions = $permissions;
		$this->resources   = $resources;
	}

        /**
	 * @return Security\IAuthorizator
	 */
	public function create()
	{
		$acl = new Security\Permission();
		if (!$this->caches->isCacheExist($this->key)) {

			// Add roles.
			foreach ($this->roles->all() as $role) {
				$parent = $this->roles->find($role->parent);
				$role->parent = $parent['name'];
				$acl->addRole($role->name, $role->parent === 0 ? NULL : $role->parent);
			}

			// Add system role.
			$acl->addRole(self::SystemRole);

			// Add resources.
			foreach ($this->resources->all() as $resource) {
				$acl->addResource($resource->name);
			}

			// Add permissions.
			foreach ($this->permissions->all() as $row) {
				$acl->{$row->allowed == 'yes' ? 'allow' : 'deny'}($row->role, $row->resource, $row->privilege);
			}

			// System role that can do everything.
			$acl->allow(self::SystemRole, Security\Permission::ALL, Security\Permission::ALL);

			// Save permissions to cache.
			$this->caches->setToCache($this->key, $acl);
		}

		// Load permissions form cache.
		if ($this->caches->isCacheExist($this->key)) {
			$acl = $this->caches->getFromCache($this->key);
			return $acl;
		}
	}

}
