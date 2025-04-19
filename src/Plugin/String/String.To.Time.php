<?php
namespace Plugin;

trait String_To_Time {

    protected function string_to_time(string $string, int $time=null): bool|int
    {
        if($time === null){
            $time = time();
        }
        return strtotime($string, $time);
    }

}