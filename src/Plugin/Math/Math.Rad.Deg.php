<?php
namespace Plugin;

trait Math_Rad_Deg {

    protected function math_rad_deg(float $radian): string
    {
        return rad2deg($radian);
    }
}