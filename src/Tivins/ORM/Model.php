<?php

namespace Tivins\ORM;

use JsonSerializable;
use ReflectionClass;
use Tivins\Database\SelectQuery;
use function PHPUnit\Framework\stringStartsWith;

abstract class Model implements JsonSerializable
{
    /**
     * Static cache storage.
     * @var array<string, array<int, static>>
     */
    protected static array $cache = [];

    /**
     * Static cache get.
     * @param int $id
     * @return static
     */
    public static function getInstance(int $id): static
    {
        if (!isset(self::$cache[static::class][$id])) {
            self::$cache[static::class][$id] = (new static())->load($id);
        }
        return self::$cache[static::class][$id];
    }

    public function __construct()
    {
        $this->initialize();
    }


    private static string $table;
    private static string $primaryKey;
    private static array $fields;

    public function getFields(): array
    {
        return static::$fields;
    }

    private function initialize(): void
    {
        if (isset(static::$table)) {
            return;
        }
        $refClass = new ReflectionClass(static::class);
        $tableAttr = $refClass->getAttributes(Table::class)[0] ?? null;
        static::$table = $tableAttr->newInstance()->name;
        $refFields = $refClass->getProperties();
        foreach ($refFields as $field) {
            $columnAttr = $field->getAttributes(Column::class)[0] ?? null;
            if ($columnAttr) {
                /** @var Column $inst */
                $inst = $columnAttr->newInstance();
                if ($inst->primary) static::$primaryKey = $field->name;
                else {
                    static::$fields[] = $field->name;
                }
            }
        }
    }

    public function load(int $id): static
    {
        $dbObj = DB::$db->select(self::$table, 't')
            ->addFields('t')
            ->condition('t.' . self::$primaryKey, $id)
            ->execute()
            ->fetch();
        if ($dbObj) {
            $this->assign((array)$dbObj);
            $this->{self::$primaryKey} = $dbObj->{self::$primaryKey} ?? 0;
        }
        return $this;
    }


    public static function __callStatic(string $name, array $arguments)
    {
        if (stringStartsWith($name, 'loadBy')) {
            $property = lcfirst(substr($name, strlen(('loadBy'))));
            if (property_exists(static::class, $property)) {
                return (new static())->loadBy([$property => $arguments[0]]);
            }
        }
        return NULL;
    }

    public function loadBy(array $conditions): static
    {
        $query = DB::$db->select(self::$table, 't')
            ->addFields('t');
        foreach ($conditions as $field => $value) {
            $query->condition($field, $value);
        }
        $dbObj = $query->execute()->fetch();
        if ($dbObj) {
            $this->assign((array)$dbObj);
            if (!self::$primaryKey)
                $this->{self::$primaryKey} = $dbObj->{self::$primaryKey} ?? 0;
        }
        return $this;
    }

    public function save(): static
    {
        if (!$this->{self::$primaryKey}) {
            DB::$db->insert(self::$table)
                ->fields($this->buildFields())
                ->execute();
            $this->{self::$primaryKey} = DB::$db->lastId();
        } else {
            DB::$db->update(self::$table)
                ->fields($this->buildFields())
                ->condition(self::$primaryKey, $this->{self::$primaryKey})
                ->execute();
        }
        return $this;
    }

    public function assign(array $data): static
    {
        foreach ($data as $key => $value) {
            if (property_exists($this, $key))
                $this->$key = $value;
        }
        return $this;
    }

    /**
     * Build an array to be used in database queries.
     * @return array
     */
    protected function buildFields(): array
    {
        $fields = [];
        foreach (static::$fields as $field) {
            $fields[$field] = $this->$field;
        }
        return $fields;
    }

    public function jsonSerialize(): array
    {
        return [self::$primaryKey => $this->{self::$primaryKey}] + $this->buildFields();
    }

    public static function getSelectQuery($alias = 't'): SelectQuery
    {
        return DB::$db->select(static::$table, $alias);
    }

    /**
     * @param array $collection
     * @return static[]
     */
    public static function mapCollection(array $collection): array
    {
        return array_map(fn($o) => self::mapObject($o), $collection);
    }

    public static function mapObject(array|object|null $object): ?static
    {
        return $object ? (new static())->assign((array)$object) : null;
    }
}