<?php
/**
 * @package Plugin\Modifier
 * @author Remco van der Velde
 * @since 2024-08-19
 * @license MIT
 * @version 1.0
 * @changeLog
 *    - all
 */


use Raxon\App;
use Raxon\App as Framework;
use Raxon\Module\Autoload;

trait Array_Binarysearch {

    protected function array_binarysearch($arr, $target): bool | int
    {
        $low = 0;
        $high = count($arr) - 1;
        while ($low <= $high) {
            $mid = floor(($low + $high) / 2);
            if ($arr[$mid] == $target) {
                return $mid; // element found, return key
            } elseif ($arr[$mid] < $target) {
                $low = $mid + 1;
            } else {
                $high = $mid - 1;
            }
        }
        return false; // element not found
    }

    function string_binarysearch_substring($arr, $x) {
        // Convert strings to lowercase or uppercase to make the search case insensitive
        $arr = strtolower($arr);
        $x   = strtolower($x);

        $low  = 0;
        $high = strlen($arr);

        while ($low < $high) {
            $mid = floor(($low + $high) / 2);

            if (substr($arr, $mid, strlen($x)) == $x) { // Check if the substring matches
                return $mid; // Found the substring
            }
            elseif (substr($arr, $mid, strlen($x)) > $x) {
                $high = $mid;
            } else {
                $low = $mid + 1;
            }
        }
        return false; // Not found the substring
    }
    /*
// Example usage:
$arr = "Hello, World! This is a test string.";
$x   = 'world';

$result = binarySearchSubstring($arr, $x);
    */


}