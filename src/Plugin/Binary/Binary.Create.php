<?php
namespace Plugin;

use Exception;
use Raxon\Cli\Bin\Controller\Bin;
use Raxon\Config;
use Raxon\Module\Dir;
use Raxon\Module\File;

trait Binary_Create
{

    /**
     * @throws Exception
     */
    protected function binary_create($name = null): void
    {
        $object = $this->object();
        $id = posix_geteuid();
        if(
            !in_array(
                $id,
                [
                    0
                ],
                true
            )
        ){
            throw new Exception('Only root can execute bin...');
        }
        if(empty($name)) {
            $name = Bin::DEFAULT_NAME;
        }
        $execute = $object->config(Config::DATA_PROJECT_DIR_BINARY) . Bin::EXE;
        Dir::create($object->config(Config::DATA_PROJECT_DIR_BINARY), Dir::CHMOD);
        $dir = Dir::name(Bin::DIR) .
            $object->config(
                Config::DICTIONARY .
                '.' .
                Config::DATA
            ) .
            $object->config('ds');
        $source = $dir . Bin::EXE;
        if(File::exist($execute)){
            File::delete($execute);
        }
        File::copy($source, $execute);
        $url_binary = $object->config(Config::DATA_PROJECT_DIR_BINARY) . \Raxon\Cli\Bin\Controller\Bin::BINARY;
        File::write($url_binary, $name . PHP_EOL);
        $url = \Raxon\Cli\Bin\Controller\Bin::TARGET . $name;
        $content = [];
        $content[] = '#!/bin/bash';
        # added $name as this was a bug in updating the cms
        $content[] = '_=' . $name . ' php ' . $execute . ' "$@"';
        $content = implode(PHP_EOL, $content);
        File::write($url, $content);
        shell_exec('chmod +x ' . $url);
        echo 'Binary created...' . PHP_EOL;
    }
}