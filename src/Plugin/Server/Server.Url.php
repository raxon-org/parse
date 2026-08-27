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

use Exception;

trait Server_Url {

    /**
     * @throws Exception
     */
    public function server_url(string $name): ?string
    {
        $object = $this->object();
        $name = str_replace('.', '-', $name);
        $url = $object->config('server.url.' . $name . '.' . $object->config('framework.environment'));
        if(empty($url)){
            throw new Exception('Server url not found, is it manually configured: ' . $name);
        }
        if(
            $url &&
            substr($url, -1, 1) !== '/'
        ){
            $url .= '/';
        }
        elseif(!$url) {
            $url = '/';
        }
        return $url;
    }
}