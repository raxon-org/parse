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

trait Data_All {

    protected function data_all(): mixed
    {
        $data = $this->data();
        return $data->data();
    }
}