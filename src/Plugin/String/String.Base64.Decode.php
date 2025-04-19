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

trait String_Base64_Decode {

    protected function string_base64_decode(string $string, array $options=[]): bool|string
    {
        $strict = (bool) $options['strict'] ?? false;
        return base64_decode($string, $strict);
    }

}