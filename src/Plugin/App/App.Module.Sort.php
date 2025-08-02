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

use Raxon\Config;
use Raxon\Module\Sort;

trait App_Module_Sort {

    protected function app_module_sort(array $list)
    {        
        return Sort::list($list);
     }
}