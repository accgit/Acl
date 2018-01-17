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
 * Privileges control.
 */
class Privileges extends BaseControl
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
		parent::__construct();
		$this->entity = $entity;
		$this->repository = $repository;
	}

	public function render()
	{
		$template = $this->template;
		$template->items = $this->repository->all();
		$template->form = $this['factory'];
		$template->setFile(__DIR__ . '/../templates/acl.privileges.latte');
		$template->render();
	}

	/**
	 * Factory.
	 * @return UI\Form
	 */
	protected function createComponentFactory()
	{
		$form = $this->factory();
		$form->addText('name', 'Název')
			->setAttribute('placeholder', 'Zadejte název akce nebo signálu.')
			->setRequired();

		$id = (int) $this->getParameter('id');
		if ($id > 0) {
			$item = $this->repository->find($id);
			if ($item) {
				$form->setDefaults($item);
			}
		}

		$form->addSubmit('send', 'Vložit');
		$form->onSuccess[] = [$this, 'process'];
		return $form;
	}

	/**
	 * Factory process.
	 * @param UI\Form
	 */
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
				$this->presenter->payload->modal = 'close';
				$this->presenter->payload->acl = 'acl';
				$this->redrawControl('items');
				$this->redrawControl('message');
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

		if (!$this->isAjax()) {
			$this->redirect('this');
		}
	}

	/**
	 * @param int
	 */
	public function handleEdit($id = 0)
	{
		try {
			$item = $this->repository->find($id);
			if ($item) {
				$form = $this['factory'];
				$form['send']->caption = 'Upravit';

				if ($this->isAjax()) {
					$this->presenter->payload->modal = 'privileges';
					$this->redrawControl('items');
					$this->redrawControl('factory');
				}
			}

		} catch (Exception $e) {
			if ($e->getCode() === 1) {
				$this->flashMessage('Akce nebyla nalezena.', 'warning');
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
	 * @param int
	 */
	public function handleDelete($id = 0)
	{
		try {
			if ($this->repository->find($id)) {
				$this->repository->delete($id);
				$this->flashMessage('Akce byla odstraněna.', 'info');

				if ($this->isAjax()) {
					$this->presenter->payload->acl = 'acl';
					$this->redrawControl('items');
					$this->redrawControl('message');
				}
			}

		} catch (Exception $e) {
			if ($e->getCode() === 1) {
				$this->flashMessage('Akce nebyla nalezena.', 'warning');

			} elseif ($e->getCode() === 1451) {
				$this->flashMessage('Akci nelze odstranit, nejprve odstrante přidělené oprávnění, které se vážou na tuto akci.', 'warning');
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
