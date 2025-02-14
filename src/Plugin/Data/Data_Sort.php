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

use Raxon\Module\Sort;

use Exception;

trait Data_Sort {

    /**
     * @throws Exception
     */
    protected function data_sort(array $list, array $sort=[], array $options=[]): array
    {
        return Sort::list($list)->with($sort, $options);
    }
}