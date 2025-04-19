<?php
namespace Plugin;

trait String_Csv_Get {

    protected function string_char(string $input, string $delimiter=',', string $enclosure='"', string $escape='\\'): string
    {
        return str_getcsv($input, $delimiter, $enclosure, $escape);
    }

}