<?php

/**
 * Easy permissions for users.
 * Copyright (c) 2017, Zdeněk Papučík
 */
namespace Component\Acl\Factory;

use Exception;
use Drago\Application;
use Nette\Application\UI;

use Component\Acl\Entity;
use Component\Acl\Repository;

/**
 * Roles factory.
 */
class Roles
{
	/**
	 * @var Application\UI\Factory
	 */
	private $factory;

	/**
	 * @var Entity\Roles
	 */
	private $entity;

	public function __construct(
		Application\UI\Factory $factory,
		Entity\Roles $entity)
	{
		$this->factory = $factory;
		$this->entity  = $entity;
	}

	public function create(Repository\Roles $roles)
	{
		$form = $this->factory->create();
		$form->addText('name', 'Název')
			->setAttribute('placeholder', 'Zadejte název role')
			->setRequired();

		$rows = [];
		foreach ($roles->all() as $role) {
			$rows[$role->roleId] = $role->name;
		}

		$form->addSelect('parent', 'Rodič', $rows)
			->setPrompt('Zvolte rodiče');

		$form->addHidden('roleId');
		$form->addSubmit('send', 'Přidat');
		$form->onSuccess[] = function (UI\Form $form, $values) use ($roles)  {
			try {
				$entity = $this->entity;
				$entity->setId($values->roleId);
				$entity->name = $values->name;
				$entity->parent = $values->parent == NULL ? 0 : $values->parent;
				$roles->save($entity);

			} catch (Exception $e) {
				if ($e->getCode() === 4) {
					$form->addError('Je nám líto, ale název této role není povolený.');

				} elseif($e->getCode() === 1062) {
					$form->addError('Je nám líto, ale tato role již exsistuje.');
				}
			}
		};
		return $form;
	}

}
