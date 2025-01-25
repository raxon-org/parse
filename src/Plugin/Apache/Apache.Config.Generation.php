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

use Raxon\App as Framework;

use Raxon\Module\Core;
use Raxon\Module\Dir;
use Raxon\Module\Data;
use Raxon\Module\File;

use Package\Raxon\Parse\Service\Parse;

trait Apache_Config_Generation {

    /**
     * @throws Exception
     */
    protected function apache_config_generation($options=[])
    {
        $start = microtime(true);
        $app = $this->object();
        $options = Core::object($options, Core::OBJECT);
        $dir = new Dir();
        $dir_mount_data = '/mnt/Vps3/Mount/Data/';
        $dir_mount_data_apache = $dir_mount_data . 'Apache/';
        $dir_available = '/etc/apache2/sites-available/';
        $config = false;
        $flags = (object) [];
        if(property_exists($options, 'config')){
            $config = $options->config;
        } else {
            throw new Exception('No config option given');
        }
//        $read = $dir->read('/mnt/Disk2/', true);
        $duration = (microtime(true) - $start) * 1000;
//        breakpoint($read);
        if(
            property_exists($options, 'server') &&
            property_exists($options->server, 'admin')
        ){
            //nothing
        } else {
            $admin = $app->config('server.admin');
            if($admin) {
                if (!property_exists($options, 'server')){
                    $options->server = (object)[];
                }
                $options->server->admin = $admin;
            } else {
                $exception = new Exception('Please configure a server admin, or provide the option (server.admin)...');
                throw $exception;
            }
        }
        if(
            property_exists($options, 'server') &&
            property_exists($options->server, 'name')
        ){
            //nothing
        } else {
            $exception = new Exception('Please provide the option (server.name)...');
            throw $exception;
        }
        if(
            property_exists($options, 'server') &&
            property_exists($options->server, 'root')
        ){
            //nothing
        } else {
            $options->server->root = $app->config('project.dir.public');
        }
        if(substr($options->server->root, -1, 1) === '/'){
            $options->server->root = substr($options->server->root, 0, -1);
        }


        if(
            property_exists($options, 'server') &&
            property_exists($options->server, 'alias') &&
            is_array($options->server->alias)
        ){
            $list = $options->server->alias;
            foreach($list as $nr => $alias){
                $explode = explode('.', $alias);
                $count = count($explode);
                if($count === 3){
                    $list[$nr] = $explode[0] . '.' . $options->server->name;
                } else {
                    throw new Exception('server alias should exist of domain and extension, for example: raxon.org');
                }
            }
            $options->server->alias = $list;
        } else {
            $url_dictionary = '/mnt/Disk2/Media/Software/Oxford/Definition/Input/Definition/';
            $dir = new Dir();
            $list_dictionary = $dir->read($url_dictionary);
            $list = [];
            foreach($list_dictionary as $nr => $file){
                if($file->type === File::TYPE){
                    $explode = explode('.', $file->name, 2);
                    $alias = $explode[0] . '.' . $options->server->name;
                    $list[] = $alias;
                }
            }
            $options->server->alias = $list;
        }
        $environment = 'production';
        $data = new Data();

//        $parse = $this->parse();

        $url = $app->config('controller.dir.data') . '002-site.' . $environment . '.conf';
        $url  = str_replace('Raxon/Parse', 'Raxon/Basic', $url);
        $read = File::read($url);
        $parse_options = clone $options;
        $parse_options->source = $url;
        unset($parse_options->class);
        $data->set('options', $parse_options);
//        unset($parse_options->source);
//        $data->set('options', $parse_options);
//        $parse = $this->parse();
//        $read = $parse->compile($read, $data, true);
//        /*
        $parse = new Parse($app, $data, $flags, $parse_options);
        $read = $parse->compile($read, $data);
        breakpoint($read);
//        */
        $url = $dir_available . $options->config;
        File::write($url, $read);

        /*
        $uuid = false;
        if(!array_key_exists('url', $options)){
            $options['url'] = false;
        }
        if(!array_key_exists('language', $options)){
            $options['language'] = 'en';
        }
        if(!array_key_exists('sex', $options)){
            $options['sex'] = 'female';
        }
        if($options['url'] !== false){
            $url = $options['url'];
            $dir = Dir::name($url);
            Dir::Create($dir, Dir::CHMOD);
        } else {
            $dir = '/mnt/Disk2/Media/Voice/';
            $uuid = Core::uuid();
            $url = $dir . $uuid . '.wav';
        }
        $param = '';
        if($options['language'] === 'nl'){
            $param .= ' -v nl';
        } else {
            $param .= ' -v en';
        }
        if($options['sex'] === 'female'){
            $param .= '+f3';
        } else {
            $param .= '';
        }
        $command = 'espeak-ng ' . $param . ' -p 50 -s 120 -w ' . $url . ' "' . escapeshellarg($speak) . '"';
        exec($command);
        File::permission($app, [
            'dir' => $dir,
            'url' => $url
        ]);
        return (object) [
            'url' => $url,
            'dir' => $dir,
            'uuid' => $uuid,
        ];
        */
     }
}