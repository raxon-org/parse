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

trait Block_data {

    protected function block_data($attribute=null, $data=null){
        d($attribute);
        ddd($data);
    }

}