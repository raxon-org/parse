<?php
namespace Plugin;

trait Math_Log_10 {

    protected function math_log_10(float $float): float
    {
        return log10($float);
    }
}