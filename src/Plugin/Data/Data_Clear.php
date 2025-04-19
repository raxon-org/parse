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

trait Data_Clear {

    protected function data_clear(): void
    {
        $data = $this->data();
        $data->clear();
    }
}