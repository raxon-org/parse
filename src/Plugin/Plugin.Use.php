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

trait Plugin_Use {

    public function plugin_use(string $use, string | null $as = null): void
    {
        $object = $this->object();
        d($as);
        ddd($use);
    }

}