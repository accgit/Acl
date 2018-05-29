<?php

/**
 * Easy permissions for users.
 * Copyright (c) 2017, Zdeněk Papučík
 */
namespace Component\Acl\Control;

use Nette\Application\UI;
use Drago\Application\UI\Factory;

/**
 * Base control.
 */
abstract class BaseControl extends UI\Control
{
	use Factory;

	/**
	 * Check is ajax reqest.
	 * @return bool
	 */
	public function isAjax()
	{
		return $this->presenter->isAjax();
	}
	
	/**
	 * @return UI\Form
	 */
	public function factory()
	{
		return $this->factory()->create();
	}

}
