<?php

use Raxon\Module\Cli;

trait Time {

    protected function time(): int
    {
        $attribute = func_get_args();
        $time= array_shift($attribute);

        $result = false;
        if(empty($attribute) && is_null($time)){
            $result = time();
        } else {
            if(is_bool($time)){
                $result = microtime($time);
            } else {
                switch(count($attribute)){
                    case 5:
                        $result = mktime($time,
                            array_shift($attribute),
                            array_shift($attribute),
                            array_shift($attribute),
                            array_shift($attribute),
                            array_shift($attribute)
                        );
                        break;
                    case 4:
                        $result = mktime($time,
                            array_shift($attribute),
                            array_shift($attribute),
                            array_shift($attribute),
                            array_shift($attribute)
                        );
                        break;
                    case 3:
                        $result = mktime($time,
                            array_shift($attribute),
                            array_shift($attribute),
                            array_shift($attribute)
                        );
                        break;
                    case 2:
                        $result = mktime($time,
                            array_shift($attribute),
                            array_shift($attribute)
                        );
                        break;
                    case 1:
                        $result = mktime($time,
                            array_shift($attribute)
                        );
                        break;
                    case 0:
                        $result = mktime($time);
                        break;
                }
            }
        }
        return $result;
    }

}