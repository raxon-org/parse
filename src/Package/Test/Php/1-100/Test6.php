<?php

require '/Application/vendor/raxon/framework/src/Debug.php';
require '/Application/vendor/raxon/framework/src/Module/Dir.php';
require '/Application/vendor/raxon/framework/src/Module/File.php';


$dir = new \Raxon\Module\Dir();

$read = $dir->read('/mnt/Vps3/Mount/Photo/');
foreach($read as $nr => $file) {
    breakpoint($file);
    $file->new = $file->url . strtolower($file->name);
    \Raxon\Module\File::move($file->url, $file->new);
}
