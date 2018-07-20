<?php

/**
 * Easy permissions for users.
 * Copyright (c) 2017, Zdeněk Papučík
 */
namespace Component\Acl\Control;
use Drago;

/**
 * Base control.
 */
abstract class Base extends Drago\Application\UI\Control
{
	use Drago\Application\UI\Factory;

	/**
	 * @return Drago\Localization\Translator
	 */
	public function translator()
	{
		$path = __DIR__ . '/../translation/cs.ini';
		return new Drago\Localization\Translator($path);
	}

}
