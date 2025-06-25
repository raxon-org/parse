<?php
namespace Plugin;

trait Locale_Set {

    protected function locale_set(int|string $category, array|int|string $locale=[]): bool | string
    {
        if(is_string($category)){
            $category = constant($category);
        }
        return setlocale($category, $locale);
    }
}