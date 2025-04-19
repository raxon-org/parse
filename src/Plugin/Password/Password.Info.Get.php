<?php
namespace Plugin;

trait Password_Info_Get {


    protected function password_info_get(string $hash): array
    {
        return password_get_info($hash);;
    }
}