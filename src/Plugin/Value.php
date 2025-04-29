<?php
namespace Plugin;

use Raxon\Exception\ObjectException;
use Raxon\Module\Core;

trait Value {
    const STRING = 'string';
    const BOOLEAN = 'boolean';
    const INTEGER = 'integer';
    const FLOAT = 'double';
    const ARRAY = 'array';
    const OBJECT = 'object';
    const NULL = 'NULL';
    const UNKNOWN = 'unknown';
    const RESOURCE = 'resource';
    const RESOURCE_CLOSED = 'resource (closed)';


    protected function value_concatenate(mixed $variable1=null, mixed $variable2=null) : string
    {
        return (string) $variable1 . (string) $variable2;
    }

    protected function value_plus_plus(mixed $variable=0): int | float
    {
        $variable += 0;
        $variable++;
        return $variable;
    }

    protected function value_minus_minus(mixed $variable=0): int |  float
    {
        $variable += 0;
        $variable--;
        return $variable;
    }

    protected function value_multiply_multiply(mixed $variable=0): int |  float
    {
        $variable += 0;
        return $variable * $variable;
    }

    protected function value_set(mixed $variable=null): mixed
    {
        return $variable;
    }

    /**
     * @throws ObjectException
     */
    protected function value_plus(mixed $variable1=null, mixed $variable2=null) : int |  float | string | array | object
    {
        $type1 = getType($variable1);
        $type2 = getType($variable2);
        if(
            $type1 === self::STRING ||
            $type2 === self::STRING
        ){
            return (string) $variable1 . (string) $variable2;
        }
        elseif(
            $type1 === self::OBJECT &&
            $type2 === self::OBJECT
        ){
            return Core::object_merge($variable1, $variable2);
        }
        elseif(
            $type1 === self::ARRAY &&
            $type2 === self::OBJECT
        ){
            return array_merge($variable1, (array) $variable2);
        }
        elseif(
            $type1 === self::OBJECT &&
            $type2 === self::ARRAY
        ){
            return Core::object_merge($variable1, (object) $variable2);
        }
        elseif(
            $type1 === self::ARRAY &&
            $type2 === self::ARRAY
        ){
            return array_merge($variable1, $variable2);
        } else {
            $variable1 += 0;
            $variable2 += 0;
            return $variable1 + $variable2;
        }
    }

    protected function value_minus(mixed $variable1=null, mixed $variable2=null): int |  float
    {
        $variable1 += 0;
        $variable2 += 0;
        return $variable1 - $variable2;
    }

    protected function value_multiply(mixed $variable1=null, mixed $variable2=null): int |  float
    {
        $variable1 += 0;
        $variable2 += 0;
        return $variable1 * $variable2;
    }

    protected function value_modulo(mixed $variable1=null, mixed $variable2=null): int |  float
    {
        return $variable1 % $variable2;
    }

    protected function value_divide(mixed $variable1=null, mixed $variable2=null): int |  float
    {
        $variable1 += 0;
        $variable2 += 0;
        if($variable2 != 0){
            return $variable1 / $variable2;
        } else {
            return INF;
        }
    }

    protected function value_smaller(mixed $variable1=null, mixed $variable2=null): bool
    {
        return $variable1 < $variable2;
    }

    protected function value_smaller_equal(mixed $variable1=null, mixed $variable2=null): bool
    {
        return $variable1 <= $variable2;
    }

    protected function value_smaller_smaller(mixed $variable1=null, mixed $variable2=null): bool | int
    {
        return $variable1 << $variable2;
    }

    protected function value_greater(mixed $variable1=null, mixed $variable2=null): bool
    {
        return $variable1 > $variable2;
    }

    protected function value_greater_equal(mixed $variable1=null, mixed $variable2=null): bool
    {
        return $variable1 >= $variable2;
    }

    protected function value_greater_greater(mixed $variable1=null, mixed $variable2=null): bool | int
    {
        return $variable1 >> $variable2;
    }

    protected function value_not_equal(mixed $variable1=null, mixed $variable2=null): bool
    {
        return $variable1 != $variable2;
    }

    protected function value_not_identical(mixed $variable1=null, mixed $variable2=null): bool
    {
        return $variable1 !== $variable2;
    }

    protected function value_equal(mixed $variable1=null, mixed $variable2=null): bool
    {
        return $variable1 == $variable2;
    }

    protected function value_identical(mixed $variable1=null, mixed $variable2=null): bool
    {
        return $variable1 === $variable2;
    }

    protected function value_and(mixed $variable1=null, mixed $variable2=null): bool
    {
        return $variable1 && $variable2;
    }

    protected function value_or(mixed $variable1=null, mixed $variable2=null): bool
    {
        return $variable1 || $variable2;
    }

    protected function value_xor(mixed $variable1=null, mixed $variable2=null): bool
    {
        return $variable1 xor $variable2;
    }

    protected function value_null_coalescing(mixed $variable1=null, mixed $variable2=null): bool
    {
        return $variable1 ?? $variable2;
    }

    protected function value_child(array|object $root, int|string  ...$children){
        while(true){
            $child = array_shift($children);
            if($child === null){
                break;
            }
            if(is_object($root) && property_exists($root, $child)){
                $root = $root->{$child};
            } elseif(is_array($root) && array_key_exists($child, $root)){
                $root = $root[$child];
            } else {
                return null;
            }
        }
        return $root;
    }
}