<?php
/**
 * same behaviour as in the template engine
 * $list = [], list is empty
 * $list = (object) [], list is not empty
 * $list = false, list is empty
 * $list = null, list is empty
 * $list = 0, list is empty
 * $list = 1, list is not empty
 * $list = '', list is empty
 * $list = '0', list is empty
 */
$list = '0';
$counter = null;
if($counter === 0){

}
elseif($list){
    echo 'List is not empty';
}
else{
    echo 'List is empty';
}