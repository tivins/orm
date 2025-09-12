# ORM

## Install

```shell
composer install tivins/orm
```

## Configuration

ORM use [Tivins/Database][1]. 

```php
use Tivins\ORM\DB;
use Tivins\Database\Database;
use Tivins\Database\Connectors\MySQLConnector;

# Define ORM/DB::$db with mysql connection. 
DB::$db = new Database(new MySQLConnector('my_database', 'root', 'secret'));
```

## basic example

### 1 - Create a model

```php
use Tivins\App\Models;
use \Tivins\ORM\Table;
use \Tivins\ORM\Column;

#[Table('books')]
class Book extends Model
{
    #[Column(primary: true)] 
    protected int $id = 0;
    
    #[Column] 
    protected string $title = '';
    
    #[Column] 
    protected string $author = '';
    
    #[Column] 
    protected int $year = 0;

    // add getters/setters,
    // and custom methods.
}
```
Usage:
```php
# create
$book = (new Book())
    ->setTitle("Le Petit Prince")
    ->setAuthor("Antoine de Saint-Exupéry")
    ->setYear(1943)
    ->save();
$book->getId(); // ex: 123

# load and update
$book = Book::getInstance(123);
$book->setTitle("Changed title")->save();
$book->getId(); // 123 

# load by
$book = (new Book())->loadBy(['name' => 'Changed title']);
$book->getId(); // 123 
```

## Collection
```php
// Get an array of objects
$books = Book::getSelectQuery('b')->addFields('b')->execute()->fetchAll();
// convert to Book[] array.
$books = Book::mapCollection($books);
```
Please, refer to [Database documentation][1] to learn more about `SelectQuery`.



[1]: https://github.com/tivins/database