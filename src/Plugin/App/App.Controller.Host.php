<?php
/**
 * @package Plugin\Modifier
 * @author Remco van der Velde
 * @since 2025-02-11
 * @license MIT
 * @version 1.0
 * @changeLog
 *    - all
 */
namespace Plugin;

use Exception;
use Raxon\App as Framework;

trait App_Controller_Host {

    /**
     * @throws Exception
     */
    protected function app_controller_host(mixed $host): string
    {
        $string = '';
        foreach($host as $property => $value){
            $string .= ' | Property: ' . $property . PHP_EOL;

        }
        return $string;
        $explode = explode('.', $host);
        foreach($explode as $nr => $value){
            $explode[$nr] = ucfirst($value);
        }
        $host = implode('.', $explode);
        return $host;

    }
}