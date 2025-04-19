<?php
namespace Plugin;

trait String_Compare_Version {

    protected function string_compare_version(string $version1, string $version2, string $operator=null): bool|int
    {
        if($operator === null){
            $result = version_compare($version1, $version2);
        } else {
            $operator = mb_strtolower($operator);
            $result = version_compare($version1, $version2, $operator);
        }
        return $result;
    }

}