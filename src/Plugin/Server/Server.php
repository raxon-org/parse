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

trait Server {

    public function server(string $attribute): mixed
    {
        $object = $this->object();
        return $object->server($attribute);
    }

}