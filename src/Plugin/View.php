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
        d($template);
        d($data);
        $url = Controller::locate($this->object(), $template);
        $read = File::read($url);
        d($read);
        $mtime = File::mtime($url);
        $parse = $this->parse();
        $storage = $this->storage();
        if(empty($data)){
            $storage->data('raxon.org.parse.view.source.url', $url);
            $storage->data('raxon.org.parse.view.source.mtime', $mtime);
            $read = $parse->compile($read, []);
        } else {
            $storage->data('raxon.org.parse.view.source.url', $url);
            $storage->data('raxon.org.parse.view.source.mtime', $mtime);

            if(
                is_object($data) &&
                get_class($data) === 'Data'
            ){
                $data_data = $data;
            } else {
                $data_data = new Data($data);
            }
            $read = $parse->compile($read, $data_data);
            $data_script = $data_data->data('script');
            $script = $data->data('script');
            if(!empty($data_script) && empty($script)){
                $data->data('script', $data_script);
            }
            elseif(!empty($data_script && !empty($script))){
                foreach($script as $nr => $value){
                    if(in_array($value, $data_script, true)){
                        unset($script[$nr]);
                    }
                }
                $data->data('script', array_merge($script, $data_script));
            }
            $data_link = $data_data->data('link');
            $link = $data->data('link');
            if(!empty($data_link) && empty($link)){
                $data->data('link', $data_link);
            }
            elseif(!empty($data_link && !empty($link))){
                foreach($link as $nr => $value){
                    if(in_array($value, $data_link, true)){
                        unset($link[$nr]);
                    }
                }
                $data->data('link', array_merge($link, $data_link));
            }
        }
        return $read;
    }

}