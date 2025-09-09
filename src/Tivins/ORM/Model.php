<?php

namespace Tivins\ORM;

use JsonSerializable;
use Tivins\Database\SelectQuery;

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


    protected string $table = '';
    protected string $primaryKey = '';

    abstract public function getFields(): array;

    public function load(int $id): static
    {
        $dbObj = DB::$db->select($this->table, 't')
            ->addFields('t')
            ->condition('t.' . $this->primaryKey, $id)
            ->execute()
            ->fetch();
        if ($dbObj) {
            $this->assign((array)$dbObj);
            $this->{$this->primaryKey} = $dbObj->{$this->primaryKey} ?? 0;
        }
        return $this;
    }

    public function loadBy(array $conditions): static
    {
        $query = DB::$db->select($this->table, 't')
            ->addFields('t');
        foreach ($conditions as $field => $value) {
            $query->condition($field, $value);
        }
        $dbObj = $query->execute()->fetch();
        if ($dbObj) {
            $this->assign((array)$dbObj);
            if (!$this->primaryKey)
                $this->{$this->primaryKey} = $dbObj->{$this->primaryKey} ?? 0;
        }
        return $this;
    }

    public function save(): static
    {
        if (!$this->{$this->primaryKey}) {
            DB::$db->insert($this->table)
                ->fields($this->buildFields())
                ->execute();
            $this->{$this->primaryKey} = DB::$db->lastId();
        } else {
            DB::$db->update($this->table)
                ->fields($this->buildFields())
                ->condition($this->primaryKey, $this->{$this->primaryKey})
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
        foreach ($this->getFields() as $field) {
            $fields[$field] = $this->$field;
        }
        return $fields;
    }

    public function jsonSerialize(): mixed
    {
        return [$this->primaryKey => $this->{$this->primaryKey}] + $this->buildFields();
    }

    public static function getSelectQuery($alias = 't'): SelectQuery
    {
        return DB::$db->select((new static())->table, $alias);
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