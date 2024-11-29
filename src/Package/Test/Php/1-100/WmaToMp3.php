<?php
$app = new \Raxon\App();

ddd($app);

$dir = new \Raxon\Module\Dir();

$read = $dir->read('/mnt/Disk2/Media/Music/Cd/Shuffle/');
foreach($read as $nr => $file) {
    $explode = explode('.', $file->url);
    $extension = array_pop($explode);
    if(strtoupper($extension) === 'WMA'){
        $file->new = implode('.', $explode) . '.mp3';
    }
    if(strtoupper($extension) === 'WAV'){
        $file->new = implode('.', $explode) . '.mp3';
    }
    exec('ffmpeg -i ' . $file->url . ' -vn -ar 44100 -ac 2 -ab 320k -f mp3 ' . $file->new);
}