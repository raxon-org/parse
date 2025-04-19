<?php
namespace Plugin;

trait Html_Textarea {

    protected function html_textarea(array $options=[]): string
    {
        $label = '';
        $textarea = '';
        $class = '';
        if(array_key_exists('class', $options)){
            $class=' class="'. $options['class'] . '"';
        }
        if(
            array_key_exists('name', $options) &&
            array_key_exists('label', $options)
        ) {
            $label = '<label for="' . $options['name'] . '"'. $class . '>' . $options['label'] . '</label><br>';
        }
        $rows = '';
        if(array_key_exists('rows', $options)){
            $rows = ' rows="' . $options['rows']. '"';
        }
        $cols = '';
        if(array_key_exists('cols', $options)){
            $cols = ' cols="' . $options['cols']. '"';
        }
        if(
            array_key_exists('name', $options) &&
            array_key_exists('value', $options)
        ){
            if(is_array($options['value'])){
                $textarea = '<textarea id="' . $options['name'] . '"'. $class . $rows . $cols .   ' name="' . $options['name'] . '">' . implode(",\n", $options['value']) . '</textarea>';
            } elseif(is_string($options['value'])) {
                $textarea = '<textarea id="' . $options['name'] . '"'. $class . $rows . $cols . ' name="' . $options['name'] . '">' . $options['value'] . '</textarea>';
            }
        }
        elseif(array_key_exists('name', $options)) {
            $textarea = '<textarea id="' . $options['name'] . '"'. $class . $rows . $cols . ' name="' . $options['name'] .'"></textarea>';
        }
        return $label . $textarea;
    }
}