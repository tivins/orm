<?php

namespace Tivins\ORM;

use Attribute;

#[Attribute(Attribute::TARGET_CLASS)]
readonly class Table
{
    public function __construct(
        public string $name
    ) {}
}