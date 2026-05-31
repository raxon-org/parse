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

use Exception;
use Raxon\Module\File;

trait Source_Type {

    /**
     * @throws Exception
     */
    protected function source_type(string $url=''): ?string
    {
        $object = $this->object();
        $options = [];
        $options['url'] = $url;
        if(!empty($options['url'])){
            $url = $options['url'];
            $extension = File::extension($url);
            $sourceType = $object->config('sourceType');
            d($extension);
            ddd($sourceType);
            if(in_array($extension, $sourceType, true)){
                return  $object->config('contentType.'.$extension);
            }
        }
        throw new Exception('Source type not found or configured. Extension: ' . $extension);
    }

}