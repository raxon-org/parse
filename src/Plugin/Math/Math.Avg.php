<?php
namespace Plugin;

trait Math_Avg {

    protected function math_avg(array $array=[], int $precision=0, int|string $mode=PHP_ROUND_HALF_UP): float|int
    {
        if(is_string($mode)){
            $mode = constant($mode);
        }
        $count = 0;
        if(empty($array)){
            $result = $count;
        } else {
            foreach($array as $number){
                $count += $number;
            }
            $result = round(($count / count($array)), $precision, $mode);
        }
        return $result;
    }
}