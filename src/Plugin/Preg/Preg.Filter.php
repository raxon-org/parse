<?php
namespace Plugin;

use Raxon\Parse\Attribute\Argument;

trait Preg_Filter {


    #[Argument(apply: "literal", count: 1, index:4)]
    protected function preg_filter(array|string $pattern=null, array|string $replacement=null, array|string $subject=null, int $limit=-1, $count=null): null|array|string
    {
        if($count !== null){
            $count = trim($count, '\'"');
            if(substr($count, 0, 1) == '$'){
                $count = substr($count, 1);
            }
            $counter = 0;
            $result = preg_filter($pattern, $replacement, $subject, $limit, $counter);
            $data = $this->data();
            $data->data($count, $counter);
        }
        elseif($limit != -1){
            $result = preg_filter($pattern, $replacement, $subject, $limit);
        } else {
            $result = preg_filter($pattern, $replacement, $subject);
        }
        return $result;
    }
}