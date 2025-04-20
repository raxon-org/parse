<?php
namespace Plugin;

use Raxon\Parse\Attribute\Argument;

trait Preg_Match_All {


    #[Argument(apply: "literal", count: 1, index:2)]
    protected function preg_match_all(string $pattern=null, string $subject=null, array $match_attribute=null, int $flags=PREG_PATTERN_ORDER, int $offset=0): null|bool|int
    {
        if(is_string($flags)){
            $flags = constant($flags);
        }
        if($match_attribute !== null){
            $match_attribute = trim($match_attribute, '\'"');
            if(substr($match_attribute, 0, 1) == '$'){
                $match_attribute = substr($match_attribute, 1);
            }
            $match = [];
            $result = preg_match_all($pattern, $subject, $match, $flags, $offset);
            $data = $this->data();
            $data->data($match_attribute, $match);
        } else {
            $result = preg_match_all($pattern, $subject);
        }
        return $result;
    }
}