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

trait Options {

    /**
     * @throws Exception
     */
    protected function options($type=''): mixed
    {
        $this->object();
        switch($type){
            case '':
            case 'default':
                return Framework::options($this->object());
            case 'command':
                return Framework::options($this->object(), $type);
            default:
                $data = new Data(Framework::options($this->object()));
                d($data);
                d($type);
                $result = $data->get($type);
                d($result);
                return $result;
        }
    }
}