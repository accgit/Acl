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
 * Privileges control.
 * @author Zdeněk Papučík
 */
class Privileges extends UI\Control
{
	/**
	 * @var Repository\Privileges
	 */
	private $privileges;

	/**
	 * @var Factory\Privileges
	 */
	private $factory;

	public function __construct(
		Repository\Privileges $privileges,
		Factory\Privileges $factory)
	{
		parent::__construct();
		$this->privileges = $privileges;
		$this->factory = $factory;
	}

	public function render()
	{
		$template = $this->template;
		$template->privileges = $this->privileges->all();
		$template->setFile(__DIR__ . '/../templates/acl.privileges.latte');
		$template->render();
	}

	/**
	 * @return Factory\Privileges
	 */
	protected function createComponentPrivileges()
	{
		$factory = $this->factory->create($this->privileges);
		$factory->onSuccess[] = function ($form) {
			$message = $form->values->privilegeId ? 'Aktualizace akce proběha v pořádku.' : 'Nová akce byla úspěšně přidána.';
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
			$data = $this->privileges->find($id);
			$form = $this['privileges'];
			$form['send']->caption = 'Aktualizovat';
			$form->setDefaults($data);

		} catch (Exception $e) {
			if ($e->getCode() === 1) {
				$this->flashMessage('Je nám líto, ale akce nebyla nalezena.', 'warning');
			}
		}
	}

	/**
	 * @param int
	 */
	public function handleDelete($id = 0)
	{
		try {
			$this->privileges->delete($id);
			$this->flashMessage('Akce byla úspěšně odstraněna.', 'info');

		} catch (Exception $e) {
			if ($e->getCode() === 1451) {
				$this->flashMessage('Je nám líto, ale akce nelze odstranit, nejprve odstrante přidělené oprávnění, které se odkazují na tuto akci.', 'warning');
			}
		}
		$this->redirect('this');
	}

}
