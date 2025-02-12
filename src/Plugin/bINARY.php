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

use Exception;

use Raxon\App as Framework;
use Raxon\Config;

use Raxon\Module\Dir;

trait App_Package_Dir {

    /**
     * @throws Exception
     */
    protected function app_package_dir($prefix='', $package=''): string
    {
        $object = $this->object();
        if(empty($prefix)){
            throw new Exception('Prefix is empty');
        }
        $explode = explode('_', $package);
        foreach($explode as $nr => $value){
            $explode[$nr] = ucfirst($value);
        }
        $package = implode('/', $explode);
        $package = Dir::ucfirst($package);

        $explode = explode('/', $package);
        if(substr($prefix, 0, -1) !== '/'){
            $prefix .= '/';
        }
        $result = $prefix . $package;
        if(Dir::is($result)){
            return $result;
        }
        $dir = $prefix;
        if($object->config(Config::POSIX_ID) === 0){
            if(!Dir::is($dir)){
                Dir::create($dir, Dir::CHMOD);
            }
            $command = 'chown www-data:www-data ' . $dir;
            exec($command);
        }
        if($object->config('framework.environment') === Config::MODE_DEVELOPMENT){
            $command = 'chmod 777 ' . $dir;
            exec($command);
        }
        foreach($explode as $nr => $value){
            $dir .= $value . '/';
            Dir::create($dir, Dir::CHMOD);
            if($object->config(Config::POSIX_ID) === 0){
                $command = 'chown www-data:www-data ' . $dir;
                exec($command);
            }
            if($object->config('framework.environment') === Config::MODE_DEVELOPMENT){
                $command = 'chmod 777 ' . $dir;
                exec($command);
            }
        }
        return $result;
    }
}