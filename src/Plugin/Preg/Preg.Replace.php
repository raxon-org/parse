<?php
namespace Plugin;

use Raxon\Parse\Attribute\Argument;

trait Preg_Replace {

    #[Argument(apply: "literal", count: 1, index:4)]
    protected function preg_replace(array|string|null $pattern=null, array|string|null $replacement=null, array|string|null $subject=null, int $limit=-1, string|null $count=null): null|string|array
    {
        if($count !== null){
            $count = trim($count, '\'"');
            if(substr($count, 0, 1) == '$'){
                $count = substr($count, 1);
            }
            $counter = 0;
            $result = preg_replace($pattern, $replacement, $subject, $limit, $counter);
            $data = $this->data();
            $data->data($count, $counter);
        }
        elseif($limit != -1){
            $result = preg_replace($pattern, $replacement, $subject, $limit);
        } else {
            $result = preg_replace($pattern, $replacement, $subject);
        }
        return $result;
    }
}