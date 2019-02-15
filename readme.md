
## ACL

Simple management of users' permissions.

## Requirements

- PHP 5.6 or higher
- composer

## Installation

```
composer require accgit/acl
```

## Install database

use the db.sql file.

## Extension registration

```
extensions:

	# simple management of users' permissions
	acl: Component\Acl\Extension
```

## Add a trait to the Presenter

```php
use Component\Acl;
```

## Add components to latte

```phtml
{control aclPrivileges}
{control aclResources}
{control aclRoles}
{snippet acl}
	{control aclPermissions}
{/snippet}
```

## Add snippet for flash messages

```phtml
{snippet message}
	<p n:foreach="$flashes as $flash" class="message {$flash->type}">{$flash->message}</p>
{/snippet}
```

## Add ajax redraw to presenter

```php
public function render()
{
	if ($this->isAjax()) {
		$this->redrawControl('message');
		$this->redrawControl('acl');
	}
}
```

## We create a query for assigning roles to users

```php
/**
 * @param int $userId
 * @return array
 */
public function findRoles($userId)
{
	return $this->db
		->fetchAll('
			SELECT r.name AS role FROM acl AS a JOIN roles AS r USING (roleId) 
			WHERE a.userId = ?', $userId);
}
```

## We will add individual roles to users

```php
foreach ($this->findRoles($row->userId) as $role) {
	$userRoles[] = $role['role'];
}
return new Security\Identity($row->userId, $userRoles, $row->toArray());
```

## Add a property annotation for the class (Presenter's Base)

```php
/**
 * @property-read array $signal
 */
```

## We will add an authorization check

```php
/**
 * Check authorization
 * @return void
 */
public function checkRequirements($element)
{
	if ($element instanceof \ReflectionClass) {

		// Redirecting the user to the sign-in form if he wants to go to the administration.
		if (!$this->user->isLoggedIn() and $this->name == 'Dashboard') {
			$this->redirect('Sign:in', [
				'backlink' => $this->storeRequest()
			]);

		// Everything else will check where the user has access.
		} elseif (!$this->user->isAllowed($this->name, $this->action)) {
			throw new \Nette\Application\ForbiddenRequestException;
		}

	// Check what the user can and can not do.
	} elseif ($element instanceof \ReflectionMethod) {
		if (!$this->user->isAllowed($this->name, $this->signal[1] ?: $this->action)) {
			throw new \Nette\Application\ForbiddenRequestException;
		}
	}
}
```

## Copy files from assets

css, js and latte.
