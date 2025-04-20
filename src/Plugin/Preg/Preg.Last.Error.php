<?php
namespace Plugin;

trait Preg_Last_Error {

    protected function preg_last_error(): null|array
    {
        $code = preg_last_error();
        $result = null;
        switch($code){
            case PREG_NO_ERROR :
                $result = [
                    'code' => $code,
                    'constant' => 'PREG_NO_ERROR'
                ];
                break;
            case PREG_INTERNAL_ERROR :
                $result = [
                    'code' => $code,
                    'constant' => 'PREG_INTERNAL_ERROR'
                ];
                break;
            case PREG_BACKTRACK_LIMIT_ERROR :
                $result = [
                    'code' => $code,
                    'constant' => 'PREG_BACKTRACK_LIMIT_ERROR'
                ];
                break;
            case PREG_RECURSION_LIMIT_ERROR :
                $result = [
                    'code' => $code,
                    'constant' => 'PREG_RECURSION_LIMIT_ERROR'
                ];
                break;
            case PREG_BAD_UTF8_ERROR :
                $result = [
                    'code' => $code,
                    'constant' => 'PREG_BAD_UTF8_ERROR'
                ];
                break;
            case PREG_BAD_UTF8_OFFSET_ERROR :
                $result = [
                    'code' => $code,
                    'constant' => 'PREG_BAD_UTF8_OFFSET_ERROR'
                ];
                break;
            case PREG_JIT_STACKLIMIT_ERROR :
                $result = [
                    'code' => $code,
                    'constant' => 'PREG_JIT_STACKLIMIT_ERROR'
                ];
                break;
        }
        return $result;
    }
}