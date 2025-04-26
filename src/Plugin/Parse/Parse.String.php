<?php
namespace Plugin;

use Exception;
use Raxon\Module\Core;
use Raxon\Module\Data;

trait Parse_String {

    /**
     * @throws Exception
     */
    protected function parse_string(mixed $mixed, array|object $data = null): mixed
    {
        $parse = $this->parse();

        if($data === null){
            $data = $this->data();
        }
        elseif(is_array($data)){
            $data = new Data($data);
        }
        elseif(is_object($data)) {
            if(get_class($data) === Data::class){
                $data = $data;
            } else {
                $data = new Data($data);
            }
        }
        $options = $parse->parse_options();
        $old_source = $options->source ?? null;
        if(is_scalar($mixed) || is_null($mixed)){
            if(is_string($mixed)){
                $hash = 'scalar_' . hash('sha256', '{"scalar": "' . $mixed . '"}');
            } else {
                $hash = 'scalar_' . hash('sha256', '{"scalar":' . $mixed . '}');
            }
        } else {
            $hash = hash('sha256', Core::object($mixed, Core::JSON_LINE));
        }
        $options->source = 'Internal_' . $hash;
        $parse->parse_options($options);
        if(!empty($parseData)){
            $result = $parse->compile($mixed, $data);
        } else {
            $result = $parse->compile($mixed, []);
        }
        if($old_source !== null){
            $options->source = $old_source;
        } else {
            unset($options->source);
        }
        $parse->parse_options($options);
        return $result;
    }
}