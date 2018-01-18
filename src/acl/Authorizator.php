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
 */
class Authorizator
{
	// Default roles.
	const
		ROLE_GUEST  = 'guest',
		ROLE_MEMBER = 'member',
		ROLE_ADMIN  = 'admin';

	// Option to specify privileges for all actions and signals.
	const
		PRIVILEGE_ALL = '*all';

	// Acl cache.
	const ACL_CACHE = 'acl.cache';

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
		$this->resources = $resources;
	}

	/**
	 * @return Security\IAuthorizator
	 */
	public function create()
	{
		$acl = new Security\Permission();
		if (!$this->caches->isCacheExist(self::ACL_CACHE)) {

			// Add roles.
			foreach ($this->roles->all() as $role) {
				$parent = $role->parent;
				if ($parent > 0) {
					$parent = $this->roles->find($parent);
				}
				$role->parent = $parent['name'];
				$acl->addRole($role->name, $role->parent === 0 ? null : $role->parent);
			}

			// Add resources.
			foreach ($this->resources->all() as $resource) {
				$acl->addResource($resource->name);
			}

			// Add permissions.
			foreach ($this->permissions->all() as $row) {
				$row->privilege === self::PRIVILEGE_ALL ? $row->privilege = Security\Permission::ALL : $row->privilege;
				$acl->{$row->allowed === 'yes' ? 'allow' : 'deny'}($row->role, $row->resource, $row->privilege);
			}

			// Admin role that can do everything.
			$acl->allow(self::ROLE_ADMIN, Security\Permission::ALL, Security\Permission::ALL);

			// Save permissions to cache.
			$this->caches->setToCache(self::ACL_CACHE, $acl);
		}

		// Load permissions form cache.
		if ($this->caches->isCacheExist(self::ACL_CACHE)) {
			$acl = $this->caches->getFromCache(self::ACL_CACHE);
			return $acl;
		}
	}

}
