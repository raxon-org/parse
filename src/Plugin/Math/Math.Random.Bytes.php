<?php
namespace Plugin;

use Exception;

trait Math_Random_Bytes {

    /**
     * @throws Exception
     */
    protected function math_random_bytes(int $length): string
    {
        return random_bytes($length);
    }
}