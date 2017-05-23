<?php

/**
 * Easy permissions for users.
 * Copyright (c) 2017, Zdeněk Papučík
 */
namespace Component\Acl\Factory;

use Dibi;
use Nette\Application\UI\Form;
use Drago\Application\UI;

use Component\Acl\Entity;
use Component\Acl\Repository;

/**
 * Roles factory.
 * @author Zdeněk Papučík
 */
class Roles
{
	/**
	 * @var UI\Forms
	 */
	private $forms;

	/**
	 * @var Entity\Roles
	 */
	private $entity;

	public function __construct(
		UI\Forms $forms,
		Entity\Roles $entity)
	{
		$this->forms  = $forms;
		$this->entity = $entity;
	}

	/**
	 * @return Forms
	 */
	public function create(Repository\Roles $roles)
	{
		$form = $this->forms->create();
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
		$form->onSuccess[] = function (Form $form, $values) use ($roles)  {
			try {

			    $entity = $this->entity;
			    $entity->setId($values->id);
			    $entity->name   = $values->name;
			    $entity->parent = $values->parent == NULL ? 0 : $values->parent;
			    $roles->save($entity);

			} catch (Dibi\Exception $e) {
				if ($e->getCode() == 1062) {
					$form->addError('Tento role již existuje, zvolte si prosím jiný.', 'error');
				}
				return;
			}
		};
		return $form;
	}

}
