# Secure Session

Secure session allows you to store sensitive information unto the session storage
while keeping it encrypted.

Set it as you normally would

```php
$secureSession = $this->get('secure_session');

$secureSession->set('email', 'drew@drew.com');
```

Getting the value

```php
$secureSession = $this->get('secure_session');

$email = $secureSession->get('email');
// will contain drew@drew.com
```

Deleting the value

```php
$secureSession = $this->get('secure_session');

$email = $secureSession->delete('email');
```
