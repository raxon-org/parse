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

trait Server_Url {

    public function server_url(string $name): ?string
    {
        $object = $this->object();
        $name = str_replace('.', '-', $name);
        $url = $object->config('server.url.' . $name . '.' . $object->config('framework.environment'));
        if(
            $url &&
            substr($url, -1, 1) !== '/'
        ){
            $url .= '/';
        }
        return $url;
    }

}