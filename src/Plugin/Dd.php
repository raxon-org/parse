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

trait Dd {

    public function dd($value, $options=[]): void
    {
        $object = $this->object();
        if(!array_key_exists('trace', $options)){
            $options['trace'] = true;
        }
        if($options['trace'] === true){
            $storage = $this->storage();
            $source = $storage->get('raxon.org.parse.view.url');
            ddd($storage);
            $tag = $object->config('package.raxon/parse.build.state.tag');
            if($source && $tag){
                if(
                    property_exists($tag, 'line') &&
                    is_object($tag->line) &&
                    property_exists($tag->line, 'start')
                ){
                    $options['trace'] =  $source . ':' . $tag->line->start . PHP_EOL;
                } else {
                    $options['trace'] =  $source . ':' . $tag->line . PHP_EOL;
                }
            }
            elseif($source){
                $options['trace'] =  $source . PHP_EOL;
            }
        }
        if(
            in_array(
                $value,
                [
                    '$this',
                    '{$this}',
                    '{{$this}}'
                ],
                true
            )
        ){
            $value = $this->storage();
        }
        dd($value, $options);
    }

}