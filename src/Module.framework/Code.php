<?php
/**
 * @author          Remco van der Velde
 * @since           04-01-2019
 * @copyright       (c) Remco van der Velde
 * @license         MIT
 * @version         1.0
 * @changeLog
 *  -    all
 */
namespace Raxon\Parse\Module;

use Raxon\Module\Data;

use Exception;

class Code {

    /**
     * @throws Exception
     */
    public static function result(Build $build, Data $storage, $type='', $selection=[], &$extra=''): mixed
    {
        $result = '';
        switch($type){
            case Build::VARIABLE_ASSIGN :
                $result = Variable::assign($build, $storage, $selection, true);
            break;
            case Build::VARIABLE_DEFINE :
                $result = Variable::define($build, $storage, $selection);
            break;
            case Build::METHOD_CONTROL :
                $result = Method::create_control($build, $storage, $selection);
            break;
            case '' :
                if(empty($selection)){
                    return null;
                } else {
                    throw new Exception('type not defined, (' . $type .')');
                }
            default:
                throw new Exception('type not defined, (' . $type .')');
        }
        return $result;
    }
}