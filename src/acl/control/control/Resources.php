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
 * Resources control.
 */
class Resources extends Base
{
	/**
	 * @var Entity\Resources
	 */
	private $entity;

	/**
	 * @var Repository\Resources
	 */
	private $repository;

	public function __construct(
		Entity\Resources $entity,
		Repository\Resources $repository)
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
		$template->setFile(__DIR__ . '/../templates/acl.resources.latte');
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
		$form->addText('name', 'form.name.resource')
			->setAttribute('placeholder', 'form.name.resource')
			->setAttribute('autocomplete', 'off')
			->setRequired('form.required');

		$form->addHidden('resourceId');
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
			$entity->setId($values->resourceId);
			$entity->name = $values->name;
			$this->repository->save($entity);
			$values->resourceId ? $this->flashMessage($this->translate('message.update.resource')) :
			$this->flashMessage($this->translate('message.insert.resource'), 'success');
			if ($this->isAjax()) {
				$form->setValues([], true);
				$this->redrawControl('items');
				$this->redrawControl('factory');
			}
		} catch (Exception $e) {
			if ($e->getCode() === 1062) {
				$form->addError('form.error.resource');
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
			$this->presenter->payload->toggle = 'resources';
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
			$this->flashMessage($this->translate('message.delete.resource'));
			if ($this->isAjax()) {
				$this->redrawControl('items');
			}
		} catch (Exception $e) {
			if ($e->getCode() === 1451) {
				$this->flashMessage($this->translate('message.remove.resource'), 'warning');
			}
		}
	}

}
