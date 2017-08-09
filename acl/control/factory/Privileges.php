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
 * Privileges factory.
 */
class Privileges
{
	/**
	 * @var Application\UI\Factory
	 */
	private $factory;

	/**
	 * @var Entity\Privileges
	 */
	private $entity;

	public function __construct(
		Application\UI\Factory $factory,
		Entity\Privileges $entity)
	{
		$this->factory = $factory;
		$this->entity  = $entity;
	}

	public function create(Repository\Privileges $privileges)
	{
		$form = $this->factory->create();
		$form->addText('name', 'Název')
			->setAttribute('placeholder', 'Zadejte název akce')
			->setRequired();

		$form->addHidden('privilegeId');
		$form->addSubmit('send', 'Přidat');
		$form->onSuccess[] = function (UI\Form $form, $values) use ($privileges) {
			try {
				$entity = $this->entity;
				$entity->setId($values->privilegeId);
				$entity->name = $values->name;
				$privileges->save($entity);

			} catch (Dibi\Exception $e) {
				if ($e->getCode() === 1062) {
					$form->addError('Je nám líto, ale tato akce již exsistuje.');
				}
			}
		};
		return $form;
	}

}
