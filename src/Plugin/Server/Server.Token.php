<?php
namespace Plugin;
/**
 * @package Plugin\Modifier
 * @author Remco van der Velde
 * @since 2024-08-19
 * @license MIT
 * @version 1.0
 * @changeLog
 *    - all
 */

trait Server_Token {

    public function server_token($value, $options=[]): ?string
    {
        if(array_key_exists('HTTP_AUTHORIZATION', $_SERVER)){
            $explode = explode('Bearer ', $_SERVER['HTTP_AUTHORIZATION'], 2);
            if(array_key_exists(1, $explode)){
                return $explode[1];
            }
        }
        return null;
    }

}