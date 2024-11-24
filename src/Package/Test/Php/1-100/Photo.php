<?php

require '/Application/vendor/raxon/framework/src/Debug.php';
require '/Application/vendor/raxon/framework/src/Module/Dir.php';
require '/Application/vendor/raxon/framework/src/Module/File.php';


$dir = new \Raxon\Module\Dir();

$read = $dir->read('/mnt/Disk2/Media/Photo/New York City/');
foreach($read as $nr => $file) {
    $explode = explode('.', $file->url);
    $extension = array_pop($explode);
    if(strtoupper($extension) === 'JPG'){
        $file->new = implode('.', $explode) . '.jpeg';
    }
    \Raxon\Module\File::move($file->url, $file->new);
}
