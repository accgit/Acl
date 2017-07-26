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
			$parent  = $this->roles->find($role->parent);
			$role->parent = $parent['name'];
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
			$message = $form->values->id ? 'Aktualizace role proběha v pořádku.' : 'Nová role byla úspěšně vytvořená.';
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
		$data = $this->roles->find($id);
		$form = $this['roles'];
		$form['send']->caption = 'Aktualizovat';
		if ($data) {
			$data->parent  = $data->parent === 0 ? NULL : $data->parent;
			$form->setDefaults($data);
		}
	}

	/**
	 * @param int
	 */
	public function handleDelete($id = 0)
	{
		$row = $this->roles->find($id);
		if ($row) {
			try {
				if (!$this->roles->findParent($row->id)) {
					$this->roles->delete($id);
					$this->flashMessage('Role byla úspěšně vymazána.', 'info');
				}

			} catch (Exception $e) {
				if ($e->getCode() === 1451) {
					$this->flashMessage('Litujeme, ale aktuální roli nelze vymazat, nejprve vymažte záznamy, které se vážou na roli.', 'error');

				} elseif($e->getCode() === 1) {
					$this->flashMessage('Litujeme, ale aktuální roli nelze vymazat, nejprve vymažte role, které ji dědí.', 'error');
				}
			}
		}
		$this->redirect('this');
	}

}
