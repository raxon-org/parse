<?php
/**
 * @package Plugin\Modifier
 * @author Remco van der Velde
 * @since 2024-08-19
 * @license MIT
 * @version 1.0
 * @changeLog
 *    - all
 */
namespace Plugin;

use Raxon\Attribute\Argument;

trait Plugin_Unset {

    #[Argument(apply: "literal", count: "*")]
    public function plugin_unset(...$attributes): void
    {
        breakpoint($attributes);
    }

}