# ORM


## basic example

```php
use Tivins\App\Models;

class User extends Model
{
    protected string $table = 'users';
    protected string $primaryKey = 'id_user';

    protected int $id_user = 0;
    protected string $login = '';
    protected string $pass = '';
    protected string $displayName = '';
    protected string $email = '';

    public function getFields(): array
    {
        return [
            'login', 'pass', 'displayName', 'email',
        ];
    }
    
    // getters/setters here
    // and custom methods...
}
```
Usage:
```php
# create
$user = (new User())
    ->setLogin("johndoe")
    ->setPassword(password_hash("secret", PASSWORD_DEFAULT))
    ->setEmail('john@example.com')
    ->save();
# load and update
$user = User::getInstance(123);
$user->setDisplayName("John!")->save();
```

