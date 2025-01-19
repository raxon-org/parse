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

use Raxon\Module\Core;

trait Array_Codepoint {

    protected function array_codepoint(): array
    {

        return Core::array_codepoint();
    }
}