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
 * Resources control.
 */
class Resources extends BaseControl
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
		$template->form = $this['factory'];
		$template->setFile(__DIR__ . '/../templates/acl.resources.latte');
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
			->setAttribute('placeholder', 'Zadejte název zdroje.')
			->setRequired('Prosím, vyplňte povinnu položku.');

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
			$entity->setId($values->resourceId);
			$entity->name = $values->name;

			$this->repository->save($entity);
			$message = $values->resourceId ? 'Zdroj byl aktualizován.' : 'Zdroj byl vložen.';
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
				$form->addError('Tento zdroj již exsistuje.');
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
					$this->presenter->payload->modal = 'resources';
					$this->redrawControl('items');
					$this->redrawControl('factory');
				}
			}

		} catch (Exception $e) {
			if ($e->getCode() === 1) {
				$this->flashMessage('Zdroj nebyl nalezen.', 'warning');
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
				$this->flashMessage('Zdroj byl odstraněn.', 'info');

				if ($this->isAjax()) {
					$this->presenter->payload->acl = 'acl';
					$this->redrawControl('items');
					$this->redrawControl('message');
				}
			}

		} catch (Exception $e) {
			if ($e->getCode() === 1) {
				$this->flashMessage('Zdroj nebyl nalezen.', 'warning');

			} elseif ($e->getCode() === 1451) {
				$this->flashMessage('Zdroj nelze odstranit, nejprve odstrante přidělené oprávnění, které se vážou na tento zdroj.', 'warning');
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
