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
			$rowsRoles[$role->roleId] = $role->name;
		}

		$form->addSelect('roleId', 'Role', $rowsRoles)
			->setPrompt('Zvolte roli')
			->setRequired();

		$rowsResource = [];
		foreach ($resources->all() as $resource) {
			$rowsResource[$resource->resourceId] = $resource->name;
		}

		$form->addSelect('resourceId', 'Zdroj', $rowsResource)
			->setPrompt('Zvolte zdroj')
			->setRequired();

		$rowsPrivilege = [];
		foreach ($privileges->all() as $privilege) {
			$rowsPrivilege[$privilege->privilegeId] = $privilege->name;
		}

		$form->addSelect('privilegeId', 'Akce', $rowsPrivilege)
			->setPrompt('Zvolte akci')
			->setRequired();

		$allowed = [
			'yes' => 'Povolit',
			'no'  => 'Zakázat'
		];

		$form->addSelect('allowed', 'Přístup:', $allowed)
			->setPrompt('Zvolte přístup')
			->setRequired();

		$form->addHidden('id');
		$form->addSubmit('send', 'Přidat');
		$form->onSuccess[] = function (UI\Form $form, $values) use ($permissions)  {
			try {
				$entity = $this->entity;
				$entity->setId($values->id);
				$entity->roleId = $values->roleId;
				$entity->resourceId  = $values->resourceId;
				$entity->privilegeId = $values->privilegeId;
				$entity->allowed = $values->allowed;
				$permissions->save($entity);

			} catch (Dibi\Exception $e) {
				\Tracy\Debugger::barDump($e);
			}
		};
		return $form;
	}

}
