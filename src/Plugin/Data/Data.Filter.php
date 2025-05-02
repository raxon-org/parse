<?php
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