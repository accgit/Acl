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
 * Resources factory.
 * @author Zdeněk Papučík
 */
class Resources
{
	/**
	 * @var Application\UI\Factory
	 */
	private $factory;

	/**
	 * @var Entity\Resources
	 */
	private $entity;

	public function __construct(
		Application\UI\Factory $factory,
		Entity\Resources $entity)
	{
		$this->factory = $factory;
		$this->entity  = $entity;
	}

	public function create(Repository\Resources $resources)
	{
		$form = $this->factory->create();
		$form->addText('name', 'Název:')
			->setRequired();

		$form->addHidden('id');
		$form->addSubmit('send', 'Přidat');
		$form->onSuccess[] = function (UI\Form $form, $values) use ($resources) {
			try {
				$entity = $this->entity;
				$entity->setId($values->id);
				$entity->name = $values->name;
				$resources->save($entity);

			} catch (Dibi\Exception $e) {
				if ($e->getCode() === 1062) {
					$form->addError('Tento zdroj již existuje, zvolte si prosím jiný.');
				}
			}
		};
		return $form;
	}

}
