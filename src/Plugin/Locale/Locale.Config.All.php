<?php
namespace Plugin;

trait Locale_Config_All {

    protected function locale_config_all(): array
    {
        return localeconv();
    }
}