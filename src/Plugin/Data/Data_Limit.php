<?php
namespace Plugin;

use Exception;
use Raxon\Module\Limit;

trait Data_Limit {

    /**
     * @throws Exception
     */
    protected function data_limit(array $list, array $limit, array $options=[], &$count=0): array
    {
        return Limit::list($list)->with($limit, $options, $count);
    }
}