<?php

$list = [];
for($i=0; $i<=0x10FFFF; $i++){
    $list[$i] = mb_chr($i);
}
