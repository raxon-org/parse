<?php
namespace Plugin;

trait Math_Round {

    protected function math_round(float|null $float=null, int $precision=0, int $mode=PHP_ROUND_HALF_UP): float
    {
        if(is_string($mode)){
            $mode = constant($mode);
        }
        return round($float, $precision, $mode);
    }
}