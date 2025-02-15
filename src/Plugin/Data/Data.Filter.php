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

use Exception;

trait Data_Filter {

    /**
     * @throws Exception
     */
    protected function data_filter(array $list=null, array $where=[]): mixed
    {
        return Filter::list($list)->where($where);
    }
}