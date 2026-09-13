<?php
namespace Plugin;

use Exception;

trait Environment {

    /**
     * @throws Exception
     */
    public function environment(string $environment=null): string
    {
        $object = $this->object();
        if($environment){
            switch($environment){
                case 'development':
                case 'staging':
                case 'test':
                case 'production':
                case 'replica':
                break;
                default:
                    throw new Exception('Invalid environment: ' . $environment . '.');
            }
        } else {
            $environment = $object->config('framework.environment');
            if($environment === null){
                throw new Exception('Environment not found aborting...');
            }
        }
        return $environment;
    }
}