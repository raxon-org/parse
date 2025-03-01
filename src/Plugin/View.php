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

use Raxon\App;
use Raxon\Module\Controller;
use Raxon\Module\Data;
use Raxon\Module\Dir;
use Raxon\Module\File;

use RaXon\Parse\Module\Parse;

use Exception;

trait View {

    /**
     * @throws Exception
     */
    protected function view(mixed $template, mixed $data=null): mixed
    {
        $object = $this->object();
        $url = Controller::locate($object, $template);
        $data = $this->view_data($data);
        $flags = App::flags($object);
        $options = App::options($object);
        $options_require = clone $options;
        $options_require->source = $url;
        unset($options_require->hash);
        unset($options_require->class);
        unset($options_require->namespace);
        $parse = new Parse($object, $data, $flags, $options_require);
        $read = File::read($url);
        return $parse->compile($read);
    }

    /**
     * @throws Exception
     */
    protected function view_data(mixed $data=null): ?Data
    {
        if(is_array($data)){
            $data = new Data($data);
        }
        elseif(
            is_object($data) &&
            $data instanceof Data
        ){
            //nothing
        }
        elseif(is_object($data)) {
            $data = new Data($data);
        } else {
            $data = $this->data();
        }
        return $data;
    }

}