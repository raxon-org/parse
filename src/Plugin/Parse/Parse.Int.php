<?php
namespace Plugin;
/**
 * @package Plugin
 * @author Remco van der Velde
 * @since 2025-02-15
 * @license MIT
 * @version 1.0
 * @changeLog
 *    - all
 */

trait Parse_Int {

    protected function parse_int(mixed $mixed): bool
    {
        return intval($mixed);
    }
}