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

    public function validate($value, $argument_nr, $validation): void
    {
        $object = $this->object();
        d($value);
        d($argument_nr);
        ddd($validation);
    }

}