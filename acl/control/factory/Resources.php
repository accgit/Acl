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
 * Resources factory.
 * @author Zdeněk Papučík
 */
class Resources
{
	/**
	 * @var UI\Forms
	 */
	private $forms;

	/**
	 * @var Entity\Resources
	 */
	private $entity;

	public function __construct(
		UI\Forms $forms,
		Entity\Resources $entity)
	{
		$this->forms  = $forms;
		$this->entity = $entity;
	}

	/**
	 * @return Forms
	 */
	public function create(Repository\Resources $resources)
	{
		$form = $this->forms->create();
		$form->addText('name', 'Název:')
			->setRequired();

		$form->addHidden('id');
		$form->addSubmit('send', 'Přidat');
		$form->onSuccess[] = function (Form $form, $values) use ($resources) {
			try {

				$entity = $this->entity;
				$entity->setId($values->id);
				$entity->name = $values->name;
				$resources->save($entity);

			} catch (Dibi\Exception $e) {
				if ($e->getCode() == 1062) {
					$form->addError('Tento zdroj již existuje, zvolte si prosím jiný.', 'error');
				}
				return;
			}
		};
		return $form;
	}

}
