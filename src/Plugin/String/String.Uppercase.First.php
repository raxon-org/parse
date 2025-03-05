<?php
/**
 * @package Plugin
 * @author Remco van der Velde
 * @since 2024-08-19
 * @license MIT
 * @version 1.0
 * @changeLog
 *    - all
 */


trait String_Uppercase_First {

    protected function string_uppercase_first(string $string): string
    {
        return ucfirst($string);
    }

}