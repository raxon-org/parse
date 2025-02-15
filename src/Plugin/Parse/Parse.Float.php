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


trait Parse_Float {

    protected function parse_float(mixed $mixed): bool
    {
        return floatval($mixed);
    }
}