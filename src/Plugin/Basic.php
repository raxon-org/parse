<?php
namespace Plugin;

use Raxon\App;

use Raxon\Module\Data;

trait Basic {

    protected App $object;
    protected Data $data;
    protected object $flags;
    protected object $options;
    protected array $local = [];
    protected array $limit = [];

    public function object(App $object=null): ?App
    {
        if($object !== null){
            $this->setObject($object);
        }
        return $this->getObject();
    }

    private function setObject(App $object): void
    {
        $this->object = $object;
    }

    private function getObject(): ?App
    {
        return $this->object;
    }

    public function data(Data $data=null): ?Data
    {
        if($data !== null){
            $this->setData($data);
        }
        return $this->getData();
    }

    private function setData(Data $data): void
    {
        $this->data = $data;
    }

    private function getData(): ?Data
    {
        return $this->data;
    }

    public function storage(Data $data=null): ?Data
    {
        if($data !== null){
            $this->setStorage($data);
        }
        return $this->getStorage();
    }

    private function setStorage(Data $data): void
    {
        $this->data = $data;
    }

    private function getStorage(): ?Data
    {
        return $this->data;
    }

    public function options(object $options=null): ?object
    {
        if($options !== null){
            $this->set_options($options);
        }
        return $this->get_options();
    }

    private function set_options(object $options): void
    {
        $this->options = $options;
    }

    private function get_options(): ?object
    {
        return $this->options;
    }

    public function flags(object $flags=null): ?object
    {
        if($flags !== null){
            $this->set_flags($flags);
        }
        return $this->get_flags();
    }

    private function set_flags(object $flags): void
    {
        $this->flags = $flags;
    }

    private function get_flags(): ?object
    {
        return $this->flags;
    }

    public function local(int $depth=0, mixed $local=null): ?object
    {
        if($local !== null){
            $this->local[$depth] = $local;
        }
        if(
            $depth !== null &&
            array_key_exists($depth, $this->local)
        ){
            return clone $this->local[$depth];
        }
        return null;
    }

    public function framework(): ?App
    {
        return $this->object();
    }

    public function limit(array $limit=null): ?array
    {
        if($limit !== null){
            $this->limit = $limit;
        }
        return $this->limit;
    }

}