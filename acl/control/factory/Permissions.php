<?php

/**
 * Easy permissions for users.
 * Copyright (c) 2017, Zdeněk Papučík
 */
namespace Component\Acl\Factory;

use Dibi;
use Drago\Application;
use Nette\Application\UI;

use Component\Acl\Entity;
use Component\Acl\Repository;

/**
 * Permissions factory.
 * @author Zdeněk Papučík
 */
class Permissions
{
	/**
	 * @var Application\UI\Factory
	 */
	private $factory;

	/**
	 * @var Entity\permissions
	 */
	private $entity;

	public function __construct(
		Application\UI\Factory $factory,
		Entity\Permissions $entity)
	{
		$this->factory = $factory;
		$this->entity  = $entity;
	}

	public function create(
		Repository\Roles $roles,
		Repository\Resources $resources,
		Repository\Privileges $privileges,
		Repository\Permissions $permissions)
	{
		$form = $this->factory->create();
		$rowsRoles = [];
		foreach ($roles->all() as $role) {
			$rowsRoles[$role->id] = $role->name;
		}

		$form->addSelect('role', 'Role:', $rowsRoles)
			->setPrompt(NULL)
			->setRequired();

		$rowsResource = [];
		foreach ($resources->all() as $resource) {
			$rowsResource[$resource->id] = $resource->name;
		}

		$form->addSelect('resource', 'Zdroj:', $rowsResource)
			->setPrompt(NULL)
			->setRequired();

		$rowsPrivilege = [];
		foreach ($privileges->all() as $privilege) {
			$rowsPrivilege[$privilege->id] = $privilege->name;
		}

		$form->addSelect('privilege', 'Akce:', $rowsPrivilege)
			->setPrompt(NULL)
			->setRequired();

		$allowed = [
			'yes' => 'Povolit',
			'no'  => 'Zakázat'
		];

		$form->addSelect('allowed', 'Přístup:', $allowed)
			->setPrompt(NULL)
			->setRequired();

		$form->addHidden('id');
		$form->addSubmit('send', 'Přidat');
		$form->onSuccess[] = function (UI\Form $form, $values) use ($permissions)  {
			try {
				$entity = $this->entity;
				$entity->setId($values->id);
				$entity->role = $values->role;
				$entity->resource  = $values->resource;
				$entity->privilege = $values->privilege;
				$entity->allowed = $values->allowed;
				$permissions->save($entity);

			} catch (Dibi\Exception $e) {
				\Tracy\Debugger::barDump($e);
			}
		};
		return $form;
	}

}
