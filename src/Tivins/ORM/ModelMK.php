<?php

namespace Tivins\ORM;

abstract class ModelMK extends Model
{
    protected array $keys = [];

    public function load(int $id): static
    {
        $q = DB::$db->select($this->table, 't')
            ->addFields('t');
        foreach ($this->keys as $key) {
            $q->condition($key, $this->$key);
        }
        $dbObj = $q->execute()->fetch();

        $this->assign((array)$dbObj);
        return $this;
    }

    public function save(): static
    {
        $q = DB::$db->select($this->table, 't')
            ->addField('t', ...$this->getFields());
        foreach ($this->keys as $key) {
            $q->condition($key, $this->$key);
        }
        $match = $q->execute()->fetch();

        if (!$match) {
            DB::$db->insert($this->table)
                ->fields($this->buildFields())
                ->execute();
        } else {
            $q = DB::$db->update($this->table)
                ->fields($this->buildFields());
            foreach ($this->keys as $key) {
                $q->condition($key, $this->$key);
            }
            $q->execute();
        }
        return $this;
    }

    public function delete(): void
    {
        $q = DB::$db->delete($this->table);
        foreach ($this->keys as $key) {
            $q->condition($key, $this->$key);
        }
        $q->execute();
    }

    public function jsonSerialize(): array
    {
        return $this->buildFields();
    }
}