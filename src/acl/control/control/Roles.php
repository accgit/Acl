<?php

/**
 * Easy permissions for users.
 * Copyright (c) 2017, Zdeněk Papučík
 */
namespace Component\Acl\Control;

use Exception;
use Nette\Application\UI;

use Component\Acl\Entity;
use Component\Acl\Repository;

/**
 * Roles control.
 */
class Roles extends BaseControl
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
		parent::__construct();
		$this->entity = $entity;
		$this->repository = $repository;
	}

	public function render()
	{
		$template = $this->template;
		$template->items = $this->buildTree($this->repository->all());
		$template->form = $this['factory'];
		$template->setFile(__DIR__ . '/../templates/acl.roles.latte');
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
		$form->addText('name', 'Název')
			->setAttribute('placeholder', 'Zadejte název role.')
			->setAttribute('autocomplete', 'off')
			->setRequired('Prosím, vyplňte povinnu položku.');

		$form->addSelect('parent', 'Rodič', $this->factoryItems())
			->setPrompt('Zvolte rodiče');

		$form->addHidden('roleId');
		$form->addSubmit('send', 'Vložit');
		$signal = $this->presenter->getSignal();
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
			$message = $values->roleId ? 'Role byla aktualizována.' : 'Role byla vložená.';
			$this->flashMessage($message, 'success');
			if ($this->isAjax()) {
				$form->setValues([], true);
				$this->presenter->payload->acl = 'acl';
				$this['factory']['parent']->setItems($this->factoryItems());
				$this->redrawControl('items');
				$this->redrawControl('message');
				$this->redrawControl('factory');
			}

		} catch (Exception $e) {
			if ($e->getCode() === 4) {
				$form->addError('Tato role není povolená.');

			} elseif($e->getCode() === 1062) {
				$form->addError('Tato role již existuje.');
			}
			if ($this->isAjax()) {
				$this->redrawControl('errors');
			}
		}
		if (!$this->isAjax()) {
			$this->redirect('this');
		}
	}

	/**
	 * @param int $id
	 */
	public function handleEdit($id = 0)
	{
		try {
			$item = $this->repository->findRole($id);
			if ($item) {
				$form = $this['factory'];
				$form['send']->caption = 'Upravit';
				if ($this->isAjax()) {
					$this->presenter->payload->toggle = 'roles';
					$this->redrawControl('items');
					$this->redrawControl('factory');
				}
			}

		} catch (Exception $e) {
			if ($e->getCode() === 1) {
				$this->flashMessage('Role nebyla nalezena.', 'warning');

			} elseif ($e->getCode() === 3) {
				$this->flashMessage('Roli není povoleno jakkoliv upravovat.', 'warning');
			}
			if ($this->isAjax()) {
				$this->redrawControl('message');
			}
		}
		if (!$this->isAjax()) {
			$this->redirect('this');
		}
	}

	/**
	 * @param int $id
	 */
	public function handleDelete($id = 0)
	{
		try {
			if ($this->repository->find($id)) {
				if (!$this->repository->findParent($id)) {
					$this->repository->delete($id);
					$this->flashMessage('Role byla odstraněna.', 'info');
					if ($this->isAjax()) {
						$this->presenter->payload->acl = 'acl';
						$this->redrawControl('items');
						$this->redrawControl('factory');
						$this->redrawControl('message');
					}
				}
			}

		} catch (Exception $e) {
			if ($e->getCode() === 1) {
				$this->flashMessage('Role nebyla nalezena.', 'warning');

			} elseif ($e->getCode() === 2) {
				$this->flashMessage('Roli nelze odstranit, nejprve odstrante role, které se vážou na tuto roli.', 'warning');

			} elseif ($e->getCode() === 4) {
				$this->flashMessage('Roli nelze odstranit.', 'warning');

			} elseif ($e->getCode() === 1451) {
				$this->flashMessage('Roli nelze odstranit, nejprve odstrante přidělené oprávnění, které se vážou na tuto roli.', 'warning');
			}
			if ($this->isAjax()) {
				$this->redrawControl('message');
			}
		}
		if (!$this->isAjax()) {
			$this->redirect('this');
		}
	}

}
