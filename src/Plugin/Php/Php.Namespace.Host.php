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


trait Php_Namespace_Host {

    protected function php_namespace_host(mixed $host): string
    {
        ddd($host);
        $explode = explode('.', $host);
        foreach($explode as $nr => $value){
            $explode[$nr] = ucfirst($value);
        }
        return implode('_', $explode);
    }
}