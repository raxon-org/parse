<?php
namespace Plugin;

trait Math_Log_1p {

    protected function math_log_1p(float $float): float
    {
        return log1p($float);
    }
}