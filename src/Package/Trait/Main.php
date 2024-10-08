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
            throw new Exception('Source not found, please provide the options -source=...');
        }
        if(File::exist($options->source) === false){
            throw new Exception('Source not found: ' . $options->source);
        }
        $object = $this->object();
        $input = File::read($options->source);
        $parse = new Parse($object, new Data(), $flags, $options);
        return $parse->compile($input);
    }
}