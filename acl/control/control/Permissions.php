<?php

/**
 * Easy permissions for users.
 * Copyright (c) 2017, Zdeněk Papučík
 */
namespace Component\Acl\Control;

use Exception;
use Nette\Application\UI;

use Component\Acl\Repository;
use Component\Acl\Factory;

/**
 * Permissions control.
 * @author Zdeněk Papučík
 */
class Permissions extends UI\Control
{
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

	/**
	 * @var Factory\Permissions
	 */
	private $factory;

	public function __construct(
		Repository\Roles $roles,
		Repository\Resources $resources,
		Repository\Privileges $privileges,
		Repository\Permissions $permissions,
		Factory\Permissions $factory)
	{
		parent::__construct();
		$this->roles = $roles;
		$this->resources = $resources;
		$this->privileges  = $privileges;
		$this->permissions = $permissions;
		$this->factory = $factory;
	}

	public function render()
	{
		$template = $this->template;
		$template->roles = $this->roles->all();
		$template->permissions = $this->permissions->all();
		$template->setFile(__DIR__ . '/../templates/acl.permissions.latte');
		$template->render();
	}

	/**
	 * @return Factory\Permissions
	 */
	protected function createComponentPermissions()
	{
		$factory = $this->factory->create($this->roles, $this->resources, $this->privileges, $this->permissions);
		$factory->onSuccess[] = function ($form) {
			$message = $form->values->id ? 'Aktualizace přístupu proběha v pořádku.' : 'Nový přístup byl úspěšně přidán.';
			$this->flashMessage($message, 'success');
			$this->redirect('this');
		};
		return $factory;
	}

	/**
	 * @param int
	 */
	public function handleEdit($id = 0)
	{
		try {
			$data = $this->permissions->find($id);
			$form = $this['permissions'];
			$form['send']->caption = 'Aktualizovat';
			$form->setDefaults($data);

		} catch (Exception $e) {
			if ($e->getCode() === 1) {
				$this->flashMessage('Je nám líto, ale nastavení přístupu nebylo nalezeno.', 'warning');
			}
		}
	}

	/**
	 * @param int
	 */
	public function handleDelete($id = 0)
	{
		try {
			$this->permissions->delete($id);
			$this->flashMessage('Přístup byl úspěšně odstraněn.', 'info');

		} catch (Exception $e) {
			\Tracy\Debugger::barDump($e);
		}
	}

}
