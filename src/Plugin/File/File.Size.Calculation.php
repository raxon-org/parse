<?php
namespace Plugin;

use Raxon\Module\File;

trait File_Size_Calculation {

    public function file_size_calculation(int|float|string $calculation=0): float|int
    {
        return File::size_calculation($calculation);
    }

}