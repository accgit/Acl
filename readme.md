
## Simple management of users' permissions

Built on Nette Framework

[![Codacy Badge](https://api.codacy.com/project/badge/Grade/e26c8a01b9674c198a68185187c469a7)](https://www.codacy.com/app/accgit/Acl?utm_source=github.com&utm_medium=referral&utm_content=accgit/Acl&utm_campaign=badger)

## Installation

Put this code into the Presenter's Base.

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
		if (!$this->user->isAllowed($this->name, $this->signal ?: $this->action)) {
			$this->messageWarning('You do not have permission.');
			$this->redirect('Homepage:');
		}
	}
}
```
