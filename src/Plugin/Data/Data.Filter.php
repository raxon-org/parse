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

use Raxon\Module\Filter;

trait Data_Filter {

    protected function data_filter(string $list, array $where=[]): mixed
    {
        return Filter::list($list)->where($where);
    }
}