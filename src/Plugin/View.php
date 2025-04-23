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

use Raxon\Module\Controller;
use Raxon\Module\Data;
use Raxon\Module\File;

use Exception;

trait View {

    /**
     * @throws Exception
     */
    protected function view(mixed $template, mixed $data=null): mixed
    {
        if(empty($template)){
            trace();
            d($data);
            die();
        }
        $url = Controller::locate($this->object(), $template);
        $read = File::read($url);
        $mtime = File::mtime($url);
        $parse = $this->parse();
        $storage = $this->storage();

        $storage->data('raxon.org.parse.view.source.url', $url);
        $storage->data('raxon.org.parse.view.source.mtime', $mtime);

        if($data === null){
            $data = $storage;
        }
        elseif(
            is_object($data) &&
            get_class($data) === 'Data'
        ){
            //nothing
        } else {
            $data = new Data($data);
        }
        $options = $parse->parse_options();
        $view_options = (object) [];
        $view_options->source = $url;
        $parse->parse_options($view_options);
        $read = $parse->compile($read, $data);
        $parse->parse_options($options);
        $data_script = $data->data('script');
        $script = $data->data('script');
        if(!empty($data_script) && empty($script)){
            $storage->data('script', $data_script);
        }
        elseif(!empty($data_script && !empty($script))){
            foreach($script as $nr => $value){
                if(in_array($value, $data_script, true)){
                    unset($script[$nr]);
                }
            }
            $storage->data('script', array_merge($script, $data_script));
        }
        $data_link = $data->data('link');
        $link = $data->data('link');
        if(!empty($data_link) && empty($link)){
            $storage->data('link', $data_link);
        }
        elseif(!empty($data_link && !empty($link))){
            foreach($link as $nr => $value){
                if(in_array($value, $data_link, true)){
                    unset($link[$nr]);
                }
            }
            $storage->data('link', array_merge($link, $data_link));
        };
        $this->storage($storage);
        return $read;
    }

}