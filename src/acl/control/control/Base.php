<?php

/**
 * Easy permissions for users.
 * Copyright (c) 2017, Zdeněk Papučík
 */
namespace Component\Acl\Control;

use Drago\Application;
use Nette\Application\UI;

/**
 * Base control.
 */
abstract class BaseControl extends UI\Control
{
	/**
	 * @var Application\UI\Factory
	 */
	public $factory;

	public function __construct(Application\UI\Factory $factory)
	{
		parent::__construct();
		$this->factory = $factory;
	}

	/**
	 * Check is ajax reqest.
	 * @return bool
	 */
	public function isAjax()
	{
		return $this->presenter->isAjax();
	}

}
