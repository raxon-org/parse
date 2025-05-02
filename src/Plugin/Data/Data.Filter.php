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
        d($list);
        d($where);
        $list = Filter::list($list)->where($where);
        ddd($list);
        return $list;
    }
}