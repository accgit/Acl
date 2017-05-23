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
 * Privileges factory.
 * @author Zdeněk Papučík
 */
class Privileges
{
	/**
	 * @var UI\Forms
	 */
	private $forms;

	/**
	 * @var Entity\Privileges
	 */
	private $entity;

	public function __construct(
		UI\Forms $forms,
		Entity\Privileges $entity)
	{
		$this->forms  = $forms;
		$this->entity = $entity;
	}

	/**
	 * @return Forms
	 */
	public function create(Repository\Privileges $privileges)
	{
		$form = $this->forms->create();
		$form->addText('name', 'Název:')
			->setRequired();

		$form->addHidden('id');
		$form->addSubmit('send', 'Přidat');
		$form->onSuccess[] = function (Form $form, $values) use ($privileges) {
			try {

			    $entity = $this->entity;
			    $entity->setId($values->id);
			    $entity->name = $values->name;
			    $privileges->save($entity);

			} catch (Dibi\Exception $e) {
				if ($e->getCode() == 1062) {
					$form->addError('Tento název akce již existuje, zvolte si prosím jiný.', 'error');
				}
				return;
			}
		};
		return $form;
	}

}
