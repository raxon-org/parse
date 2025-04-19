<?php
namespace Plugin;

trait String_Metaphone {

    protected function string_metaphone(string $string, int $max_phonemes=0): string
    {
        return metaphone($string, $max_phonemes);
    }

}