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

    public function parse_options(object $options=null): ?object
    {
        if($options !== null){
            $this->parse_set_options($options);
        }
        return $this->parse_get_options();
    }

    private function parse_set_options(object $options): void
    {
        $this->options = $options;
    }

    private function parse_get_options(): ?object
    {
        return $this->options;
    }

    public function parse_flags(object $flags=null): ?object
    {
        if($flags !== null){
            $this->parse_set_flags($flags);
        }
        return $this->parse_get_flags();
    }

    private function parse_set_flags(object $flags): void
    {
        $this->flags = $flags;
    }

    private function parse_get_flags(): ?object
    {
        return $this->flags;
    }

    public function local($depth=0, $local=null): ?object
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

}