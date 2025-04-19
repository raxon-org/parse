<?php
namespace Plugin;

trait Math_Ceil {

    protected function math_ceil(float|int $float): float
    {
        return ceil($float);
    }
}