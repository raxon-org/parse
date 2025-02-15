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

trait Parse_Bool {

    protected function parse_bool(mixed $mixed): bool
    {
        return boolval($mixed);
    }
}