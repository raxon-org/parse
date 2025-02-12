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

use Exception;

use Raxon\App as Framework;
use Raxon\Config;

use Raxon\Module\Core;

trait Binary {

    /**
     * @throws Exception
     */
    protected function binary($fallback=null): ?string
    {
        $object = $this->object();
        return Core::binary($object) ?? $fallback;
    }
}