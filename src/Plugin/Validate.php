<?php
/**
 * @package Plugin\Modifier
 * @author Remco van der Velde
 * @since 2024-08-19
 * @license MIT
 * @version 1.0
 * @changeLog
 *    - all
 */
namespace Plugin;

trait Validate {

    public function validate_argument(mixed $value, string $type, array $validation, int $argument_nr=null): void
    {
//        trace();
//        $object = $this->object();
//        d($value);
//        d($argument_nr);
//        d($validation);
    }

}