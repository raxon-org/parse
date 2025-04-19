<?php
namespace Plugin;

trait Debug_Backtrace {

    public function debug_backtrace(int $options=DEBUG_BACKTRACE_PROVIDE_OBJECT, $limit=0): array
    {
        return debug_backtrace($options, $limit);
    }

}