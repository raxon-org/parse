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

trait Parse_Url {

    protected function parse_url(string $string): array
    {
        $result = [];
        parse_str($string, $result);
        return $result;
    }
}