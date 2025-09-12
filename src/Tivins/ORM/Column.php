<?php

namespace Tivins\ORM;

use Attribute;

#[Attribute(Attribute::TARGET_PROPERTY)]
readonly class Column
{
    public function __construct(
        public ?string $name = null,
        public bool $primary = false,
        public bool $autoIncrement = false,
    ) {}
}