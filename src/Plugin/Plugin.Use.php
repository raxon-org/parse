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

use Raxon\Parse\Module\Parse;

trait Plugin_Use {

    public function plugin_use(string $use, string | null $as = null): void
    {
        $object = $this->object();
        $use_class = $object->config(Parse::CONFIG . '.build.use.class') ?? [];
        if($as !== null){
            $use_class[] = $use . ' as ' . $as;
        } else {
            $use_class[] = $use;
        }        
        d($use_class);
        $object->config(Parse::CONFIG . '.build.use.class', $use_class);        
    }

}