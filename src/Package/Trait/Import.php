<?php
namespace Package\Raxon\Parse\Trait;

use Raxon\App;

use Raxon\Exception\DirectoryCreateException;
use Raxon\Exception\FileWriteException;
use Raxon\Exception\ObjectException;
use Raxon\Module\Core;
use Raxon\Module\File;

use Raxon\Node\Model\Node;

use Exception;
trait Import {

    /**
     * @throws DirectoryCreateException
     * @throws ObjectException
     * @throws FileWriteException
     */
    public function role_system(): void
    {
        $object = $this->object();
        $package = $object->request('package');
        if($package){
            $node = new Node($object);
            $node->role_system_create($package);
        }
    }

    /**
     * @throws FileWriteException
     * @throws ObjectException
     * @throws Exception
     */
    public function system_parse(): void
    {
        $object = $this->object();
        $node = new Node($object);

        $options = App::options($object);

        $options->url = $object->config('project.dir.vendor') .
            '/raxon/parse/Data/System.Parse' .
            $object->config('extension.json')
        ;
        $class = 'System.Parse';

        $import = $node->import($class, $node->role_system(), $options);
        ddd($import);


        ddd($url);
        $node->patch($class, $node->role_system(), $record);
    }
}