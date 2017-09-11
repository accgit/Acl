<?php

/**
 * Easy permissions for users.
 * Copyright (c) 2017, Zdeněk Papučík
 */
namespace Component\Acl\Control;
use Nette\Application\UI;

/**
 * Base control.
 */
abstract class BaseControl extends UI\Control
{
	public function __construct()
	{
		parent::__construct();
	}

	/**
	 * Check is ajax reqest.
	 * @return bool
	 */
	public function isAjax()
	{
		return $this->presenter->isAjax();
	}

	/**
	 * Form factory.
	 * @return \Nette\Application\UI\Form
	 */
	public function factory()
	{
		return new UI\Form;
	}

	// Close modal.
	public function handleClose()
	{
		if ($this->isAjax()) {
			$this->presenter->payload->modal = 'close';
			$this->redrawControl('factory');
		}
	}

}
