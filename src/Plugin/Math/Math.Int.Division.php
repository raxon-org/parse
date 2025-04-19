<?php
namespace Plugin;

trait Math_Int_Division {

    protected function math_int_division(int $dividend, int $divisor): int
    {
        return intdiv($dividend, $divisor);
    }
}