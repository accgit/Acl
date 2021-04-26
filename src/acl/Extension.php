<?php

/**
 * Easy permissions for users.
 * Copyright (c) 2017, Zdeněk Papučík
 */
namespace Component\Acl;
use Nette;

/**
 * Adding dependencies to the system container.
 */
class Extension extends Nette\DI\CompilerExtension
{
	public function loadConfiguration()
	{
		$config = $this->loadFromFile(__DIR__ . '/conf.neon');
		$this->compiler->loadDefinitionsFromConfig($config['services']);
	}
}
