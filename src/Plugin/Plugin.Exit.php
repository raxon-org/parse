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

trait Plugin_Exit {

    protected function plugin_exit(int $status=0): void
    {
        exit($status);
    }
}