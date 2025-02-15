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

use Raxon\Attribute\Validate;

trait Config_ALl {

    #[Validate(
        result: "mixed"
    )]
    protected function config_all(): mixed
    {
        $object = $this->object();
        return $object->config();
    }
}