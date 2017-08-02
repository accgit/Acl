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
use Component\Acl\Authorizator;

/**
 * Roles control.
 * @author Zdeněk Papučík
 */
class Roles extends UI\Control
{
	/**
	 * @var Repository\Roles
	 */
	private $roles;

	/**
	 * @var Factory\Roles
	 */
	private $factory;

	public function __construct(
		Repository\Roles $roles,
		Factory\Roles $factory)
	{
		parent::__construct();
		$this->roles = $roles;
		$this->factory = $factory;
	}

	public function render()
	{
		$roles = [];
		foreach ($this->roles->all() as $role) {
			$roleParent = $role->parent;
			if ($roleParent > 0) {
				$roleParent = $this->roles->find($roleParent);
			}
			$role->parent = $roleParent['name'];
			$role->parent = $role->parent === 0 ? NULL : $role->parent;
			$roles[] = $role;
		}
		$template = $this->template;
		$template->roles = $roles;
		$template->setFile(__DIR__ . '/../templates/acl.roles.latte');
		$template->render();
	}

	/**
	 * @return Factory\Roles
	 */
	protected function createComponentRoles()
	{
		$factory = $this->factory->create($this->roles);
		$factory->onSuccess[] = function ($form) {
			$message = $form->values->roleId ? 'Aktualizace role proběha v pořádku.' : 'Nová role byla úspěšně přidána.';
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
			$data = $this->roles->find($id);
			if ($data->name === Authorizator::ROLE_GUEST or $data->name === Authorizator::ROLE_MEMBER or $data->name === Authorizator::ROLE_ADMIN) {
				$this->flashMessage('Je nám líto, ale roli není povoleno jakkoliv upravovat.', 'warning');
				$this->redirect('this');
			}
			$form = $this['roles'];
			$form['send']->caption = 'Aktualizovat';
			$data->parent = $data->parent === 0 ? NULL : $data->parent;
			$form->setDefaults($data);

		} catch (Exception $e) {
			if ($e->getCode() === 1) {
				$this->flashMessage('Je nám líto, ale role nebyla nalezená.', 'warning');
			}
		}
	}

	/**
	 * @param int
	 */
	public function handleDelete($id = 0)
	{
		try {
			if (!$this->roles->findParent($id)) {
				$this->roles->delete($id);
				$this->flashMessage('Role byla úspěšně odstraněna.', 'info');
			}

		} catch (Exception $e) {
			if ($e->getCode() === 1) {
				$this->flashMessage('Je nám líto, ale role nebyla nalezená.', 'warning');

			} elseif ($e->getCode() === 2) {
				$this->flashMessage('Je nám líto, ale roli nelze odstranit, nejprve odstrante role, které se odkazují na tuto roli.', 'warning');

			} elseif ($e->getCode() === 3) {
				$this->flashMessage('Je nám líto, ale roli není povoleno odstranit.', 'warning');

			} elseif ($e->getCode() === 1451) {
				$this->flashMessage('Je nám líto, ale roli nelze odstranit, nejprve odstrante přidělené oprávnění, které se odkazují na tuto roli.', 'warning');
			}
		}
		$this->redirect('this');
	}

}
