<?php
namespace Plugin;

trait Math_Min {

    protected function math_min(array ...$array): mixed
    {
        return min(...$array);
    }
}