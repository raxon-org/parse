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

trait String_Base64_Encode {

    protected function string_base64_encode(string $string): bool|string
    {
        return base64_encode($string);
    }

}