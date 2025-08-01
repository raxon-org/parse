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
use Raxon\Module\Core;

trait Binary
{

    /**
     * @throws Exception
     */
    protected function binary(string|null $fallback = null): ?string
    {
        $object = $this->object();
        return Core::binary($object) ?? $fallback;
    }
}