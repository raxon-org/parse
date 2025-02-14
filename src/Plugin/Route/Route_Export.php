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

use Raxon\App;

use Raxon\Module\Route;

trait Route_Export {

    protected function route_export(): array
    {
        $object = $this->object();
        $route = $object->data(App::ROUTE);
        $list = $route->data();
        $result = [];
        foreach($list as $nr => $record){
            $result[$nr] = Route::controller($record);
        }
        return $result;
    }
}