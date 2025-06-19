<?php
/**
 * @author          Remco van der Velde
 * @since           2025-06-19
 * @version         1.0
 * @changeLog
 *     -    all
 */
/*
$chars = chars();
$count = count($chars);
$words = [];
for($i = 0; $i < (100000000); $i++){
    $words[] = random_word($chars, $count);
}
*/
//    $write = implode(' ', $words);
//    $write .= implode(' ', $words);
//    $write .= implode(' ', $words);
//
$begin = microtime(true);
$url = '/mnt/Disk2/Test/data.txt';
//    $size = file_put_contents($url, $write);
$start = microtime(true);
$size = filesize($url);
$read = file_get_contents($url);
$duration_read = microtime(true) - $start;
echo 'File read time: ' . time_format($duration_read, '') . '; size: ' . size_format($size) . PHP_EOL;
$part_size = (1024 * 1024) * 4;
$parts = ceil($size / $part_size);
$split = mb_str_split($read, $part_size);
$offset = 100;
$start= microtime(true);
for($i = 0; $i < $parts; $i++){
    $shmop = shmop_open($offset + $i, 'n', 0600, $part_size);
    $memory_data = $split[$i] . "\0";
    if($shmop){
        shmop_write($shmop, $memory_data);
    }
    $duration_write = microtime(true) - $start;
    echo 'Memory write time: ' . time_format($duration_write, '', true) . ' ' . size_format(($part_size * ($i + 1)) / $duration_write) . '/sec' . PHP_EOL;
}
$start= microtime(true);
$read = [];
for($i = 0; $i < $parts; $i++){
    $shmop = shmop_open($offset + $i, 'a', 0, 0);
    if($shmop){
        $memory_data = shmop_read($shmop, 0, $part_size);
        $explode = explode("\0", $memory_data);
        if(array_key_exists(1, $explode)){
            $read[$i] = $explode[0];
        } else {
            $read[$i] = $memory_data;
        }
    }
    $duration_write = microtime(true) - $start;
    echo 'Memory read time: ' . time_format($duration_write, '', true) . ' ' . size_format(($part_size * ($i + 1)) / $duration_write) . '/sec' . PHP_EOL;
}
$duration = microtime(true) - $begin;
echo 'Total duration: ' . time_format($duration,'', true) . PHP_EOL;

function size_format(float|int $size=0): string
{
    if($size < 1024){
        return '0 B';
    }
    elseif($size < 1024 * 1024){
        return round($size / 1024, 2) . ' KB';
    }
    elseif($size < 1024 * 1024 * 1024){
        return round($size / 1024 / 1024, 2) . ' MB';
    }
    elseif($size < 1024 * 1024 * 1024 * 1024){
        return round($size / 1024 / 1024 / 1024, 2) . ' GB';
    }
    elseif($size < 1024 * 1024 * 1024 * 1024 * 1024){
        return round($size / 1024 / 1024 / 1024 / 1024, 2) . ' TB';
    }
    elseif($size < 1024 * 1024 * 1024 * 1024 * 1024 * 1024){
        return round($size / 1024 / 1024 / 1024 / 1024 / 1024, 2) . ' PB';
    } else {
        return round($size / 1024 / 1024 / 1024 / 1024 / 1024 / 1024, 2) . ' EB';
    }

}

function time_format(int $seconds=0, string $string='in', $compact=false): string
{
    $days = floor($seconds / (3600 * 24));
    $hours = floor(($seconds / 3600) % 24);
    $minutes = floor(($seconds / 60) % 60);
    $seconds = $seconds % 60;
    if($days > 0){
        if($compact){
            $string .= $days . ' ' . 'd' . ' ';
        } else {
            if($days === 1){
                $string .= $days . ' ' . 'day' . ' ' . 'and' . ' ';
            } else {
                $string .= $days . ' ' . 'days' . ' ' . 'and' . ' ';
            }
        }
    }
    if($hours > 0){
        if($compact){
            $string .= $hours . ' ' . 'h' . ' ';
        } else {
            if($hours === 1){
                $string .= $hours . ' ' . 'hour' . ' ' . 'and' . ' ';
            } else {
                $string .= $hours . ' ' . 'hours' . ' ' . 'and' . ' ';
            }
        }
    }
    if ($minutes > 0){
        if($compact){
            $string .= $minutes . ' ' . 'min' . ' ';
        } else {
            if($minutes === 1){
                $string .= $minutes . ' ' . 'minute' . ' ' . 'and' . ' ';
            } else {
                $string .= $minutes . ' ' . 'minutes' . ' ' . 'and' . ' ';
            }
        }

    }
    if($seconds < 1){
        if($days === 0 && $hours === 0 && $minutes === 0){
            if($compact){
                $string = round($seconds, 3) * 1000 . ' ' . 'msec';
            } else {
                $string = 'almost there';
            }
        } else {
            if($compact){
                $string .= $seconds . ' ' . 'sec';
            } else {
                $string .= $seconds . ' ' . 'seconds';
            }
        }

    } else {
        if($compact){
            $string .= $seconds . ' ' . 'sec';
        } else {
            if($seconds === 1){
                $string .= $seconds . ' ' . 'second';
            } else {
                $string .= $seconds . ' ' . 'seconds';
            }
        }
    }
    return $string;
}

function random_word($chars, $count){
    $wordlength = rand(2, 25);
    $word = '';
    for($i=0; $i < $wordlength; $i++){
        $letter = rand(0, $count - 1);
        $word .= $chars[$letter];
    }
    return $word;
}

function chars(): array
{
    $chars = [];
    $chars[] = 'a';
    $chars[] = 'b';
    $chars[] = 'c';
    $chars[] = 'd';
    $chars[] = 'e';
    $chars[] = 'f';
    $chars[] = 'g';
    $chars[] = 'h';
    $chars[] = 'i';
    $chars[] = 'j';
    $chars[] = 'k';
    $chars[] = 'l';
    $chars[] = 'm';
    $chars[] = 'n';
    $chars[] = 'o';
    $chars[] = 'p';
    $chars[] = 'q';
    $chars[] = 'r';
    $chars[] = 's';
    $chars[] = 't';
    $chars[] = 'u';
    $chars[] = 'v';
    $chars[] = 'w';
    $chars[] = 'x';
    $chars[] = 'y';
    $chars[] = 'z';

    foreach($chars as $char){
        $chars[] = strtoupper($char);
    }
    return $chars;
}