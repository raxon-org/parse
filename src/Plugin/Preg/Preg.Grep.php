<?php
namespace Plugin;

trait Preg_Grep {

    protected function preg_grep(string $pattern=null, array $input=[], int|string  $flags=0): bool|array
    {
        if(is_string($flags)){
            $flags = constant($flags);
        }
        if($flags != 0){
            $result = preg_grep($pattern, $input, $flags);
        } else {
            $result = preg_grep($pattern, $input);
        }
        return $result;
    }
}