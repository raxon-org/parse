<?php
namespace Plugin;
/**
 * @package Plugin
 * @author Remco van der Velde
 * @since 2025-02-15
 * @license MIT
 * @version 1.0
 * @changeLog
 *    - all
 */

use Raxon\Module\Data;

trait Parse_String {

    protected function parse_string(mixed $mixed, array | Data $data = []): mixed
    {
        $parse = $this->parse();
        if(!empty($parseData)){
            return $parse->compile($mixed, $data);
        } else {
            return $parse->compile($mixed, []);
        }
    }
}