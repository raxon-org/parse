<?php
namespace Plugin;

trait Preg_Split {

    protected function preg_split(string $pattern=null, string $subject=null, int $limit=-1, int $flags=0): bool|array
    {
        if(is_string($flags)){
            $flags = constant($flags);
        }
        return preg_split($pattern, $subject, $limit, $flags);
    }
}