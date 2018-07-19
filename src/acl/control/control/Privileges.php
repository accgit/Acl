<?php

/**
 * Easy permissions for users.
 * Copyright (c) 2017, Zdeněk Papučík
 */
namespace Component\Acl\Control;

use Drago;
use Exception;
use Nette\Application\UI;

use Component\Acl\Entity;
use Component\Acl\Repository;

/**
 * Privileges control.
 */
class Privileges extends Drago\Application\UI\Control
{
	use Drago\Application\UI\Factory;

	/**
	 * @var Entity\Privileges
	 */
	private $entity;

	/**
	 * @var Repository\Privileges
	 */
	private $repository;

	public function __construct(
		Entity\Privileges $entity,
		Repository\Privileges $repository)
	{
		parent::__construct();
		$this->entity = $entity;
		$this->repository = $repository;
	}

	public function render()
	{
		$template = $this->template;
		$template->items = $this->repository->all();
		$template->form  = $this['factory'];
		$template->setFile(__DIR__ . '/../templates/acl.privileges.latte');
		$template->render();
	}

	/**
	 * @return UI\Form
	 */
	protected function createComponentFactory()
	{
		$form = $this->createForm();
		$form->addText('name', 'Název')
			->setAttribute('placeholder', 'Zadejte název akce.')
			->setAttribute('autocomplete', 'off')
			->setRequired('Prosím, vyplňte povinnu položku.');

		$form->addHidden('privilegeId');
		$form->addSubmit('send', 'Vložit');
		$signal = $this->getSignal();
		if ($signal) {
			if (in_array('edit', $signal)) {
				$item = $this->repository->find($this->getParameter('id'));
				$form->setDefaults($item);
			}
		}
		$form->onSuccess[] = [$this, 'process'];
		return $form;
	}

	public function process(UI\Form $form)
	{
		try {
			$values = $form->values;
			$entity = $this->entity;
			$entity->setId($values->privilegeId);
			$entity->name = $values->name;
			$this->repository->save($entity);
			$message = $values->privilegeId ? 'Akce byla aktualizována.' : 'Akce byla vložená.';
			$this->flashMessage($message, 'success');
			if ($this->isAjax()) {
				$form->setValues([], true);
				$this->redrawControl('items');
				$this->redrawControl('factory');
			}
		} catch (Exception $e) {
			if ($e->getCode() === 1062) {
				$form->addError('Tento akce již exsistuje.');
			}
			if ($this->isAjax()) {
				$this->redrawControl('errors');
			}
		}
	}

	/**
	 * @param int $id
	 */
	public function handleEdit($id = 0)
	{
		$item =  $this->repository->find($id);
		$item ?: $this->error();
		$form =  $this['factory'];
		$form['send']->caption = 'Upravit';
		if ($this->isAjax()) {
			$this->presenter->payload->toggle = 'privileges';
			$this->redrawControl('items');
			$this->redrawControl('factory');
		}
	}

	/**
	 * @param int $id
	 */
	public function handleDelete($id = 0)
	{
		$item =  $this->repository->find($id);
		$item ?: $this->error();
		try {
			$this->repository->delete($id);
			$this->flashMessage('Akce byla odstraněna.');
			if ($this->isAjax()) {
				$this->redrawControl('items');
			}
		} catch (Exception $e) {
			if ($e->getCode() === 1451) {
				$this->flashMessage('Akci nelze odstranit, nejprve odstrante přidělené oprávnění, které se vážou na tuto akci.', 'warning');
			}
		}
	}

}
