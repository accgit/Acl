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
 * Permissions control.
 */
class Permissions extends BaseControl
{
	/**
	 * @var Entity\Permissions
	 */
	private $entity;

	/**
	 * @var Repository\Roles
	 */
	private $roles;

	/**
	 * @var Repository\Resources
	 */
	private $resources;

	/**
	 * @var Repository\Privileges
	 */
	private $privileges;

	/**
	 * @var Repository\Permissions
	 */
	private $permissions;

	public function __construct(
		Entity\Permissions $entity,
		Repository\Roles $roles,
		Repository\Resources $resources,
		Repository\Privileges $privileges,
		Repository\Permissions $permissions)
	{
		parent::__construct();
		$this->entity = $entity;
		$this->roles  = $roles;
		$this->resources = $resources;
		$this->privileges  = $privileges;
		$this->permissions = $permissions;
	}

	public function render()
	{
		$template = $this->template;
		$template->roles = $this->roles->findRoles();
		$template->rules = $this->permissions->rules();
		$template->privileges = $this->permissions->privileges();
		$template->resources = $this->permissions->resources();
		$template->form = $this['factory'];
		$template->setFile(__DIR__ . '/../templates/acl.permissions.latte');
		$template->render();
	}

	/**
	 * Factory.
	 * @return UI\Form
	 */
	protected function createComponentFactory()
	{
		$form  = $this->createForm();
		$roles = [];
		foreach ($this->roles->all() as $role) {
			$roles[$role->roleId] = $role->name;
		}

		$form->addSelect('roleId', 'Role', $roles)
			->setPrompt('Zvolte roli')
			->setRequired('Prosím, vyplňte povinnu položku.');

		$resources = [];
		foreach ($this->resources->all() as $resource) {
			$resources[$resource->resourceId] = $resource->name;
		}

		$form->addSelect('resourceId', 'Zdroj', $resources)
			->setPrompt('Zvolte zdroj')
			->setRequired('Prosím, vyplňte povinnu položku.');

		$privileges = [];
		foreach ($this->privileges->all() as $privilege) {
			$privileges[$privilege->privilegeId] = $privilege->name;
		}

		$form->addSelect('privilegeId', 'Akce', $privileges)
			->setPrompt('Zvolte akci')
			->setRequired('Prosím, vyplňte povinnu položku.');

		$allowed = [
			'yes' => 'Povolit',
			'no'  => 'Zakázat'
		];

		$form->addSelect('allowed', 'Přístup:', $allowed)
			->setPrompt('Zvolte přístup')
			->setRequired('Prosím, vyplňte povinnu položku.');

		$form->addHidden('id');
		$form->addSubmit('send', 'Vložit');
		$signal = $this->presenter->getSignal();
		if ($signal) {
			if (in_array('edit', $signal)) {
				$item = $this->permissions->find($this->getParameter('id'));
				$form->setDefaults($item);
			}
		}
		$form->onSuccess[] = [$this, 'process'];
		return $form;
	}

	/**
	 * Factory process.
	 * @param UI\Form
	 */
	public function process(UI\Form $form)
	{
		$values = $form->values;
		$entity = $this->entity;
		$entity->setId($values->id);
		$entity->roleId = $values->roleId;
		$entity->resourceId  = $values->resourceId;
		$entity->privilegeId = $values->privilegeId;
		$entity->allowed = $values->allowed;
		$this->permissions->save($entity);
		$message = $values->id ? 'Oprávnění bylo aktualizováno.' : 'Oprávnění bylo vloženo.';
		$this->flashMessage($message, 'success');

		if ($this->isAjax()) {
			$form->setValues([], true);
			$this->redrawControl('items');
			$this->redrawControl('message');
			$this->redrawControl('factory');
		}
	}

	/**
	 * @param int $id
	 */
	public function handleEdit($id = 0)
	{
		try {
			$item = $this->permissions->find($id);
			if ($item) {
				$form = $this['factory'];
				$form['send']->caption = 'Upravit';

				if ($this->isAjax()) {
					$this->presenter->payload->toggle = 'permissions';
					$this->redrawControl('items');
					$this->redrawControl('factory');
				}
			}

		} catch (Exception $e) {
			if ($e->getCode() === 1) {
				$this->flashMessage('Oprávnění nebylo nalezeno.', 'warning');
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
			if ($this->permissions->find($id)) {
				$this->permissions->delete($id);
				$this->flashMessage('Oprávnění bylo odebráno.', 'info');

				if ($this->isAjax()) {
					$this->redrawControl('items');
					$this->redrawControl('factory');
					$this->redrawControl('message');
				}
			}

		} catch (Exception $e) {
			if ($e->getCode() === 1) {
				$this->flashMessage('Oprávnění nebylo nalezeno.', 'warning');
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
