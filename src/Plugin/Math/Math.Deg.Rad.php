<?php
namespace Plugin;

trait Math_Deg_Rad {

    protected function math_deg_rad(float $degrees): string
    {
        return deg2rad($degrees);
    }
}