<?php
namespace Plugin;

use Raxon\Module\Dir;

trait Dir_Delete {

    protected function dir_delete(string $directory): bool
    {
        $dir = new Dir();
        return $dir->delete($directory);
    }

}