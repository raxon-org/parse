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

trait Language {

    protected function language(string $attribute=null): string
    {
        $object = $this->object();
        if($attribute === null){
            $language = $object->session('language');
            if($language === null){
                $object->session('language', $object->config('framework.default.language'));
                $language = $object->session('language');
            }
        } else {
            $object->session('language', $attribute);
            $language = $attribute;
        }
        return $language;
    }

}