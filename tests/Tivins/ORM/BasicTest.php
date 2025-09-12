<?php

namespace Tivins\ORM;

use PHPUnit\Framework\TestCase;
use Tivins\Database\Connectors\SQLiteConnector;
use Tivins\Database\Database;
use Tivins\Database\Exceptions\ConnectionException;

/**
 * @method static loadByTitle(string $string):?static
 */
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

    public function getId(): int
    {
        return $this->id;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function setTitle(string $title): static
    {
        $this->title = $title;
        return $this;
    }

    public function getAuthor(): string
    {
        return $this->author;
    }

    public function setAuthor(string $author): static
    {
        $this->author = $author;
        return $this;
    }

    public function getYear(): int
    {
        return $this->year;
    }

    public function setYear(int $year): static
    {
        $this->year = $year;
        return $this;
    }
}

final class BasicTest extends TestCase
{
    /**
     * Create a database and some tables for the tests.
     *
     * @throws ConnectionException
     */
    private function prepare(): void
    {
        // We need to do the database preparation only one time.
        if (isset(DB::$db)) {
            return;
        }

        error_reporting(E_ALL);
        ini_set('display_errors', '1');

        // Temp database, with a fixed name in order to debug.
        $file = __dir__ . '/_test_db.sqlite';

        // Be sure to create a new database:
        unlink($file);

        // Define ORM Database connection:
        DB::$db = new Database(new SQLiteConnector($file));

        // Create a simple table:
        DB::$db->create('books')
            ->addAutoIncrement('id')
            ->addString('title')
            ->addString('author')
            ->addInteger('year', 0)
            ->setEngine('')
            ->execute();
    }

    /**
     * @throws ConnectionException
     */
    public function testSimple()
    {
        $this->prepare();

        $sample = (object)[
            'title' => 'Book 1',
            'author' => 'Author 1',
            'year' => 2000
        ];


        $book = (new Book())
            ->setTitle($sample->title)
            ->setAuthor($sample->author)
            ->setYear(2000)
            ->save();

        $this->assertEquals(1, $book->getId());

        $book = Book::getInstance(1);
        $this->assertEquals(1, $book->getId());
        $this->assertEquals($sample->title, $book->getTitle());
        $this->assertEquals(2000, $book->getYear());
        $this->assertEquals($sample->author, $book->getAuthor());

        $book = Book::loadByTitle($sample->title);
        $this->assertEquals(1, $book->getId());
    }
}