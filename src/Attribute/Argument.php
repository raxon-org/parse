<?php
namespace Raxon\Parse\Attribute;

use Attribute;

#[Attribute(Attribute::TARGET_CLASS | Attribute::TARGET_METHOD)]

class Argument
{
    public function __construct(
        public string $apply='',
        public int|array $count=0,
        public int|array $index=0,
    ) {

    }
}
