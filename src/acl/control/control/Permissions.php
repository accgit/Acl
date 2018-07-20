<?php

/**
 * Easy permissions for users.
 * Copyright (c) 2017, Zdeněk Papučík
 */
namespace Component\Acl\Control;

use Component\Acl\Entity;
use Component\Acl\Repository;

use Nette\Application\UI;

/**
 * Permissions control.
 */
class Permissions extends Base
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
		$template->resources  = $this->permissions->resources();
		$template->form = $this['factory'];
		$template->setFile(__DIR__ . '/../templates/acl.permissions.latte');
		$template->setTranslator($this->translator());
		$template->render();
	}

	/**
	 * @return UI\Form
	 */
	protected function createComponentFactory()
	{
		$form  = $this->createForm();
		$form->setTranslator($this->translator());
		$roles = [];
		foreach ($this->roles->all() as $role) {
			$roles[$role->roleId] = $role->name;
		}

		$form->addSelect('roleId', 'form.role', $roles)
			->setPrompt('form.select.role')
			->setRequired('form.required');

		$resources = [];
		foreach ($this->resources->all() as $resource) {
			$resources[$resource->resourceId] = $resource->name;
		}

		$form->addSelect('resourceId', 'form.resource', $resources)
			->setPrompt('form.select.resource')
			->setRequired('form.required');

		$privileges = [];
		foreach ($this->privileges->all() as $privilege) {
			$privileges[$privilege->privilegeId] = $privilege->name;
		}

		$form->addSelect('privilegeId', 'form.privilege', $privileges)
			->setPrompt('form.select.privilege')
			->setRequired('form.required');

		$allowed = [
			'yes' => 'form.allowed.yes',
			'no'  => 'form.allowed.no'
		];

		$form->addSelect('allowed', 'form.allowed', $allowed)
			->setPrompt('form.select.allowed')
			->setRequired('form.required');

		$form->addHidden('id');
		$form->addSubmit('send', 'form.send');
		$signal = $this->getSignal();
		if ($signal) {
			if (in_array('edit', $signal)) {
				$item = $this->permissions->find($this->getParameter('id'));
				$form->setDefaults($item);
			}
		}
		$form->onSuccess[] = [$this, 'process'];
		return $form;
	}

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
		$message = $values->id ? $this->translate('message.update.permissions') : $this->translate('message.insert.permissions');
		$this->flashMessage($message, 'success');
		if ($this->isAjax()) {
			$form->setValues([], true);
			$this->redrawControl('items');
			$this->redrawControl('factory');
		}
	}

	/**
	 * @param int $id
	 */
	public function handleEdit($id = 0)
	{
		$item =  $this->permissions->find($id);
		$item ?: $this->error();
		$form =  $this['factory'];
		$form['send']->caption = 'form.send.update';
		if ($this->isAjax()) {
			$this->presenter->payload->toggle = 'permissions';
			$this->redrawControl('items');
			$this->redrawControl('factory');
		}
	}

	/**
	 * @param int $id
	 */
	public function handleDelete($id = 0)
	{
		$item =  $this->permissions->find($id);
		$item ?: $this->error();
		$this->permissions->delete($id);
		$this->flashMessage($this->translate('message.delete.permissions'));
		if ($this->isAjax()) {
			$this->redrawControl('items');
			$this->redrawControl('factory');
		}
	}

}
