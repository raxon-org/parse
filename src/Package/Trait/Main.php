<?php
namespace Package\Raxon\Parse\Trait;

use Raxon\Module\Data;
use Raxon\Module\File;

use Raxon\Parse\Module\Parse;

use Exception;


trait Main {

    /**
     * @throws Exception
     */
    public function compile($flags, $options): mixed {
        if(!property_exists($options, 'source')){
            throw new Exception('Source not found, please provide the options -source=...');
        }
        if(File::exist($options->source) === false){
            throw new Exception('Source not found: ' . $options->source);
        }
        $extension = File::extension($options->source);
        if(
            in_array(
                $extension,
                [
                    'json',
                    'jsonl'
                ],
                true
            ) &&
            !property_exists($options, 'type')
        ){
            $options->type = 'data';
        }
        if(!property_exists($options, 'type')){
            $options->type = 'string';
        }
        $object = $this->object();
        switch($options->type){
            case 'data':
                $object->config('package.raxon/parse.build.state.source.url', $options->source);
                $parse =  $object->compile_read($options->source, null, $flags, $options);
                if($parse){
                    return $parse->data();
                }
                return null;
            case 'string':
            default:
                $input = File::read($options->source);
                $object->config('package.raxon/parse.build.state.source.url', $options->source);
                $parse = new Parse($object, new Data(), $flags, $options);
                return $parse->compile($input);
        }
    }
}