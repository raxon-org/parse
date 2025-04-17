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

use Raxon\Parse\Attribute\Argument;

trait Plugin_Unset {

    #[Argument(apply: "literal", count: "*")]
    public function plugin_unset(...$attributes): void
    {
        ddd($attributes);
        $data = $this->storage();
        foreach($attributes as $unset){
            if(substr($unset, 0, 1) == '$'){
                $attribute = substr($unset, 1);
            } else {
                $attribute = $unset;
            }
            $data->data('delete', $attribute);
        }
    }
}