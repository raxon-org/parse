<?php
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