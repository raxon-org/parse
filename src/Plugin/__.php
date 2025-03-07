<?php
namespace Plugin;

trait __ {

    protected function __(string $attribute): mixed
    {
        $object = $this->object();
        $language = $object->session('language');
        if($language === null){
            $language = $object->session('language', $object->config('framework.default.language'));
        }
        $test = $object->data('translation');
        if(empty($test)){
            return '{{import.translation()}} missing or corrupted translation file...' . PHP_EOL;
        }
        $translation = $object->data('translation.' . $language);
        if(
            is_object($translation) &&
            property_exists($translation, $attribute)
        ){
            return $translation->{$attribute};
        } else {
            $translation = $object->data('translation.' . $object->config('framework.default.language'));
            if(
                is_object($translation) &&
                property_exists($translation, $attribute)
            ){
                return $translation->{$attribute};
            } else {
                return $attribute;
            }
        }
    }

}