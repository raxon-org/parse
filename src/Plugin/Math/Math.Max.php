<?php
namespace Plugin;

trait Math_Max {

    protected function math_max(array ...$array): mixed
    {
        return max(...$array);
    }
}