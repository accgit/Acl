<?php

/**
 * Easy permissions for users.
 * Copyright (c) 2017, Zdeněk Papučík
 */
namespace Component\Acl\Control;

use Component\Acl\Entity;
use Component\Acl\Repository;

use Exception;
use Nette\Application\UI;

/**
 * Privileges control.
 */
class Privileges extends Base
{
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
		$this->entity = $entity;
		$this->repository = $repository;
	}

	public function render()
	{
		$template = $this->template;
		$template->items = $this->repository->all();
		$template->form  = $this['factory'];
		$template->setFile(__DIR__ . '/../templates/acl.privileges.latte');
		$template->setTranslator($this->translator());
		$template->render();
	}

	/**
	 * @return UI\Form
	 */
	protected function createComponentFactory()
	{
		$form = $this->createForm();
		$form->setTranslator($this->translator());
		$form->addText('name', 'form.name')
			->setAttribute('placeholder', 'form.name')
			->setAttribute('autocomplete', 'off')
			->setRequired('form.required');

		$form->addHidden('privilegeId');
		$form->addSubmit('send', 'form.send');
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
			$values->privilegeId ? $this->flashMessage($this->translate('message.update.privilege')) :
			$this->flashMessage($this->translate('message.insert.privilege'), 'success');
			if ($this->isAjax()) {
				$form->setValues([], true);
				$this->redrawControl('items');
				$this->redrawControl('factory');
			}
		} catch (Exception $e) {
			if ($e->getCode() === 1062) {
				$form->addError('form.error.privilege');
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
		$form['send']->caption = 'form.send.update';
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
			$this->flashMessage($this->translate('message.delete.privilege'));
			if ($this->isAjax()) {
				$this->redrawControl('items');
			}
		} catch (Exception $e) {
			if ($e->getCode() === 1451) {
				$this->flashMessage($this->translate('message.remove.privilege'), 'warning');
			}
		}
	}

}
