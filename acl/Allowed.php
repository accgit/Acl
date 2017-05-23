<?php

/**
 * Easy permissions for users.
 * Copyright (c) 2017, Zdeněk Papučík
 */
namespace Component\Acl;

use Nette;
use Nette\Application;
use Nette\Security;

/**
 * Protected access.
 * @author Zdeněk Papučík
 */
class Allowed
{
	use Nette\SmartObject;

	/**
	 * @var Security\User
	 */
	private $user;

	public function __construct(Security\User $user)
	{
		$this->user = $user;
	}

	/**
	 * Check the user's permissions.
	 * @param string
	 * @param string
	 * @throws Application\ForbiddenRequestException
	 */
	public function isAllowedUser($resource, $privilege)
	{
		if (!$this->user->isAllowed($resource, $privilege)) {
			throw new Application\ForbiddenRequestException();
		}
	}

}
