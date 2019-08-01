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
 * Roles control.
 */
class Roles extends Base
{
	/**
	 * @var Entity\Roles
	 */
	private $entity;

	/**
	 * @var Repository\Roles
	 */
	private $repository;

	public function __construct(
		Entity\Roles $entity,
		Repository\Roles $repository)
	{
		$this->entity = $entity;
		$this->repository = $repository;
	}

	public function render()
	{
		$template = $this->template;
		$template->items = $this->buildTree($this->repository->all());
		$template->form  = $this['factory'];
		$template->setFile(__DIR__ . '/../templates/acl.roles.latte');
		$template->setTranslator($this->translator());
		$template->render();
	}

	/**
	 * @param array $items
	 * @param int $parent
	 * @return array
	 */
	private function buildTree($items, $parent = 0)
	{
		$arr = [];
		foreach ($items as $item) {
			if ($item->parent === $parent) {
				$chils = $this->buildTree($items, $item->roleId);
				isset($chils) === true ? $item->children = $chils : null;
				$arr[] = $item;
			}
		}
		return $arr;
	}

	/**
	 * @return array
	 */
	private function factoryItems()
	{
		$arr = [];
		foreach ($this->repository->all() as $item) {
			$arr[$item->roleId] = $item->name;
		}
		return $arr;
	}

	/**
	 * @return UI\Form
	 */
	protected function createComponentFactory()
	{
		$form = $this->createForm();
		$form->setTranslator($this->translator());
		$form->addText('name', 'form.name.role')
			->setAttribute('placeholder', 'form.name.role')
			->setAttribute('autocomplete', 'off')
			->setRequired('form.required');

		$form->addSelect('parent', 'form.role', $this->factoryItems())
			->setPrompt('form.select.role');

		$form->addHidden('roleId');
		$form->addSubmit('send', 'form.send');
		$signal = $this->getSignal();
		if ($signal) {
			if (in_array('edit', $signal)) {
				$item = $this->repository->find($this->getParameter('id'));
				$item->parent = $item->parent === 0 ? null : $item->parent;
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
			$entity->setId($values->roleId);
			$entity->name = $values->name;
			$entity->parent = $values->parent === null ? 0 : $values->parent;
			$this->repository->save($entity);
			$values->roleId ? $this->flashMessage($this->translate('message.update.role')) :
			$this->flashMessage($this->translate('message.insert.role'), 'success');
			if ($this->isAjax()) {
				$form->setValues([], true);
				$this['factory']['parent']->setItems($this->factoryItems());
				$this->redrawControl('items');
				$this->redrawControl('factory');
			}
		} catch (Exception $e) {
			if ($e->getCode() === 1062) {
				$form->addError('form.error.role');
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
		try {
			$allowed = $this->repository->isAllowed($item);
			if ($allowed) {
				$form = $this['factory'];
				$form['send']->caption = 'Upravit';
				if ($this->isAjax()) {
					$this->presenter->payload->toggle = 'roles';
					$this->redrawControl('items');
					$this->redrawControl('factory');
				}
			}
		} catch (Exception $e) {
			if ($e->getCode() === 3) {
				$this->flashMessage($this->translate('message.not.allowed.role'), 'warning');
			}
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
			$allowed = $this->repository->isAllowed($item);
			if ($allowed) {
				if (!$this->repository->findParent($id)) {
					$this->repository->delete($id);
					$this->flashMessage($this->translate('message.delete.role'));
					if ($this->isAjax()) {
						$this->redrawControl('items');
						$this->redrawControl('factory');
					}
				}
			}
		} catch (Exception $e) {
			if ($e->getCode() === 2) {
				$this->flashMessage($this->translate('message.remove.role.3'), 'warning');

			} elseif ($e->getCode() === 3) {
				$this->flashMessage($this->translate('message.remove.role.2'), 'warning');

			} elseif ($e->getCode() === 1451) {
				$this->flashMessage($this->translate('message.remove.role'), 'warning');
			}
		}
	}

}
