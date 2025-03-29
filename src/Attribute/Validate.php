<?php
namespace Raxon\Parse\Attribute;

use Attribute;

#[Attribute(Attribute::TARGET_CLASS | Attribute::TARGET_METHOD)]

class Validate
{
    public function __construct(
        public array $argument=[],
        public string $result='mixed',
    ) {

    }
}
