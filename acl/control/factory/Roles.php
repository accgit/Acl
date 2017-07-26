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
 * Roles factory.
 * @author Zdeněk Papučík
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
		$form->addText('name', 'Název:')
			->setRequired();

		$rows = [];
		foreach ($roles->all() as $role) {
			$rows[$role->id] = $role->name;
		}

		$form->addSelect('parent', 'Převzít roli:', $rows)
			->setPrompt(NULL);

		$form->addHidden('id');
		$form->addSubmit('send', 'Přidat');
		$form->onSuccess[] = function (UI\Form $form, $values) use ($roles)  {
			try {

				$entity = $this->entity;
				$entity->setId($values->id);
				$entity->name   = $values->name;
				$entity->parent = $values->parent == NULL ? 0 : $values->parent;
				$roles->save($entity);

			} catch (Dibi\Exception $e) {
				if ($e->getCode() == 1062) {
					$form->addError('Tato role již existuje, zvolte si prosím jinou.', 'error');
				}
				return;
			}
		};
		return $form;
	}

}
