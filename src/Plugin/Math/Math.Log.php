<?php
namespace Plugin;

trait Math_Log {

    protected function math_log(float $float, float $base=M_E): float
    {
        if(is_string($base)){
            $base = constant($base);
        }
        return log($float, $base);
    }
}