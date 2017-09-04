
## ACL

[![Codacy Badge](https://api.codacy.com/project/badge/Grade/e26c8a01b9674c198a68185187c469a7)](https://www.codacy.com/app/accgit/Acl?utm_source=github.com&utm_medium=referral&utm_content=accgit/Acl&utm_campaign=badger)

Simple management of users' permissions.

## Requirements

- PHP 7.0.8 or higher
- [Nette Framework](https://github.com/nette/nette)

## Installation

**1) Put this code into the Presenter's Base.**

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
		} elseif (!$this->user->isAllowed($this->name, 'default')) {
			$this->messageWarning('You do not have permission.');
			$this->redirect('Homepage:');
		}

	// Check what the user can and can not do.
	} elseif ($element instanceof \ReflectionMethod) {
		if (!$this->user->isAllowed($this->name, $this->signal[1] ?: $this->action)) {
			$this->messageWarning('You do not have permission.');
			$this->redirect('Homepage:');
		}
	}
}
```

**2) Add a property annotation for the class (Presenter's Base).**

```
@property-read array $signal
```

**3) We create a query for assigning roles to users.**

```php
/**
 * Returned record by id.
 * @param int
 * @return array
 */
public function findRoles($userId)
{
	return $this->db
		->query('SELECT r.name AS role FROM acl AS a JOIN roles AS r USING (roleId) WHERE a.userId = ?', $userId);
}
```

**4) We go through the roles that are assigned to users and return them to the identity.**

```php
$roles = $this->repository->findRoles($row->userId);
foreach ($roles as $role) {
	$userRoles[] = $role['role'];
}
return new Security\Identity($row->userId, $userRoles, $row->toArray());
```

**5) Installing dependencies via composer.**

```json
{
	"require": {
		"drago-ex/cache": "~1.0.0",
		"dibi/dibi": "~3.0.0",
	}
}
```

**6) Include [conf.neon](https://github.com/accgit/acl/blob/master/src/acl/conf.neon) in ACL component to Configuration File or register to bootstrap class.**

```php
$configurator->addConfig(__DIR__ . '/components/acl/config.nenon');
```

**7) Inject Component\Acl to Presenter and create factory.**

```php
/**
 * @return Acle
 */
protected function createComponentAcl()
{
	return $this->acl;
}
```

**8) In the template, call acl components.**

```latte
{control acl-roles}
{control acl-privileges}
{control acl-resources}
{control acl-permissions}
```

**9) Copy and pase files from www foldier.**
