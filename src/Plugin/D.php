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

trait D {

    public function d($value, $options=[]): void
    {
        $object = $this->object();
        if(!array_key_exists('trace', $options)){
            $options['trace'] = true;
        }
        if($options['trace'] === true){
            $source = $object->config('package.raxon/parse.build.state.source.url');
            $tag = $object->config('package.raxon/parse.build.state.tag');
            if($source && $tag){
                if(
                    is_object($tag) &&
                    property_exists($tag, 'line') &&
                    is_object($tag->line) &&
                    property_exists($tag->line, 'start')
                ){
                    $options['trace'] =  $source . ':' . $tag->line->start . PHP_EOL;
                }
                elseif(
                    is_object($tag) &&
                    property_exists($tag, 'line')
                ) {
                    $options['trace'] =  $source . ':' . $tag->line . PHP_EOL;
                }
                elseif(
                    is_array($tag) &&
                    array_key_exists('line', $tag) &&
                    is_array($tag['line']) &&
                    array_key_exists('start', $tag['line'])
                ){
                    $options['trace'] =  $source . ':' . $tag['line']['start'] . PHP_EOL;
                }
                elseif(
                    is_array($tag) &&
                    array_key_exists('line', $tag)
                ) {
                    $options['trace'] =  $source . ':' . $tag['line'] . PHP_EOL;
                }
            }
        }
        d($value, $options);
    }

}