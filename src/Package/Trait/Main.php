<?php
namespace Package\Raxon\Parse\Trait;

use Raxon\App;
use Raxon\Config;

use Raxon\Module\Data;
use Raxon\Module\Dir;
use Raxon\Module\File;
use Raxon\Module\Cli;

use Package\Raxon\Parse\Service\Parse;
use Package\Raxon\Parse\Service\Token;
use Package\Raxon\Parse\Service\Build;

use Exception;


trait Main {

    /**
     * @throws Exception
     */
    public function compile($flags, $options): mixed {
        if(!property_exists($options, 'source')){
            throw new Exception('Source not found');
        }
        if(File::exist($options->source) === false){
            throw new Exception('Source not found');
        }
        $object = $this->object();
        $input = File::read($options->source);
        $parse = new Parse($object, new Data(), $flags, $options);
        $compile = $parse->compile($input);
        ddd($compile);

        /*
        if(
            property_exists($options,'duration') &&
            $options->duration === true
        ){
            $result['duration'] = round((microtime(true) - $object->config('time.start')) * 1000, 2) . 'ms';
            return $result;
        }
        */
        return null;
    }
}