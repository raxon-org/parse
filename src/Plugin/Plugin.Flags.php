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
use Raxon\Module\Data;

trait Plugin_Flags {

    /**
     * @throws Exception
     */
    protected function plugin_flags($type=''): array|object
    {
        $this->object();         
        switch($type){
            case '':
            case '#default':
                return Framework::flags($this->object());
            case '#command':
                return Framework::flags($this->object(), $type);
            default:
                $options = Framework::flags($this->object());
                $data = new Data($options);
                return $data->get($type);
        }        
    }
}