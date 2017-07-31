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
 * Resources control.
 * @author Zdeněk Papučík
 */
class Resources extends UI\Control
{
	/**
	 * @var Repository\Resources
	 */
	private $resources;

	/**
	 * @var Factory\Resources
	 */
	private $factory;

	public function __construct(
		Repository\Resources $resources,
		Factory\Resources $factory)
	{
		parent::__construct();
		$this->resources = $resources;
		$this->factory = $factory;
	}

	public function render()
	{
		$template = $this->template;
		$template->resources = $this->resources->all();
		$template->setFile(__DIR__ . '/../templates/acl.resources.latte');
		$template->render();
	}

	/**
	 * @return Factory\Resources
	 */
	protected function createComponentResources()
	{
		$factory = $this->factory->create($this->resources);
		$factory->onSuccess[] = function ($form) {
			$message = $form->values->resourceId ? 'Aktualizace zdroje proběha v pořádku.' : 'Nový zdroj byl úspěšně vytvořen.';
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
			$data = $this->resources->find($id);
			$form = $this['resources'];
			$form['send']->caption = 'Aktualizovat';
			$form->setDefaults($data);

		} catch (Exception $e) {
			if ($e->getCode() === 1) {
				$this->flashMessage('Je nám líto, ale záznam nebyl nalezen.', 'error');
			}
		}
	}

	/**
	 * @param int
	 */
	public function handleDelete($id = 0)
	{
		$row = $this->resources->find($id);
		if ($row) {
			try {
				$this->resources->delete($id);
				$this->flashMessage('Zdroj byl úspěšně vymazán.', 'info');

			} catch (Exception $e) {
				if ($e->getCode() === 1451) {
					$this->flashMessage('Litujeme, ale aktuální zdroj nelze vymazat, nejprve vymažte záznamy, které se vážou na zdroj.', 'error');
				}
			}
		}
		$this->redirect('this');
	}

}
