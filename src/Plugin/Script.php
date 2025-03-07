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

trait Script {

    public function script(): void
    {
        $object = $this->object();
        $args = func_get_args();
        d($args);
    }

}