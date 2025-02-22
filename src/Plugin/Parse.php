<?php
namespace Plugin;

use Raxon\Parse\Module\Parse as Parser;

trait Parse {

    protected Parser $parse;

    public function parse(Parser $parse=null): ?Parser
    {
        if($parse !== null){
            $this->setParse($parse);
        }
        return $this->getParse();
    }

    private function setParse(Parser $parse): void
    {
        $this->parse = $parse;
    }

    private function getParse(): ?Parser
    {
        return $this->parse;
    }

}