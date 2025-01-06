# Encryption/Decryption token

This example shows how to use JWT and Encryption to create your own encription service

`EncryptionInterface` have two methods:

```php
public function encrypt($payload, $options);
public function decrypt($token, $options);
```

Any new Encryption method should implement and maintain the contract's methods.

How to use JWT ?
In our controller we can call the JWT service encrypt/decrypt function and pass the string and options required by
JWT.

```php
// get the encrypted token
$options = [
    'issuer' => 'WebComposer',
    'audience' => 'casino1',
    'expire_time' => time() + 3600,
];

$payload = [
    'username' => 'ashwini',
    'session' => '1231421',
];

$token = $this->get('jwt_encryption')->encrypt($payload, $options);
```

```php
// decrypt the token
$options = [
    'issuer' => 'WebComposer',
    'audience' => 'casino1',
];

$decrypt = $this->get('jwt_encryption')->decrypt($token, $options);
```

For more information regarding, read about [JWT](https://jwt.io/introduction/)
