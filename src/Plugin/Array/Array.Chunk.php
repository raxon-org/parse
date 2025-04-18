<?php
namespace Plugin;

trait Array_Chunk {

    protected function array_chunk(array $array, int $size=1, $preserve_key=false): array
    {
        return array_chunk($array, $size, $preserve_key);
    }
}