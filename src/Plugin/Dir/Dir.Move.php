<?php
namespace Plugin;

use Exception;
use Raxon\Module\Dir;

trait Dir_Move {

    protected function dir_move(string $source, string $target, bool $overwrite=false): bool
    {
        try {
            return Dir::move($source, $target, $overwrite);
        }
        catch (Exception $e) {
            return false;
        }
    }

}