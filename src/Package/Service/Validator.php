<?php
namespace Package\Raxon\Parse\Service;

use Raxon\App;

use Raxon\Module\Core;
use Raxon\Module\Dir;
use Raxon\Module\File;

use Exception;

use Raxon\Exception\ObjectException;
use Raxon\Exception\DirectoryCreateException;

class Validator
{

    /**
     * @throws DirectoryCreateException
     * @throws Exception
     */
    private static function dir_ramdisk(App $object): string
    {
        $posix_id = $object->config('posix.id');
        $dir_ramdisk = $object->config('ramdisk.url');
        $dir_ramdisk_user = $dir_ramdisk .
            $posix_id .
            $object->config('ds')
        ;
        $dir_ramdisk_parse = $dir_ramdisk_user .
            'Parse' .
            $object->config('ds')
        ;
        if(!Dir::is($dir_ramdisk_user)){
            Dir::create($dir_ramdisk_user,  Dir::CHMOD);
        }
        if(!Dir::is($dir_ramdisk_parse)){
            Dir::create($dir_ramdisk_parse,  Dir::CHMOD);
        }
        if($posix_id !== 0){
            File::permission($object, [
                'url' => $dir_ramdisk_user,
                'parse' => $dir_ramdisk_parse
            ]);
        }
        return $dir_ramdisk_parse;
    }

    /**
     * @throws ObjectException
     * @throws Exception
     */
    public static function validate(App $object, $flags, $options, $string): bool | string
    {
        $source = $options->source ?? '';
        $dir = Validator::dir_ramdisk($object);
        $url = $dir . 'Validate-' . hash('sha256', $string) . $object->config('extension.php');
        if(File::exist($url) === false){
            File::write($url, '<?php ' . PHP_EOL . $string . PHP_EOL);
        }
        $init = $object->config('core.execute.stream.init');
        $object->config('core.execute.stream.init', true);
        // Use PHP's built-in syntax checker
        Core::execute($object, 'php -l ' . escapeshellarg($url), $output, $notification);
        if($init){
            $object->config('core.execute.stream.init', $init);
        } else {
            $object->config('delete', 'core.execute.stream.init');
        }
        // Check the output to see if any syntax errors were found
        if (strpos($output, 'No syntax errors detected') !== false) {
            return true;
        } else {
            if($notification !== ''){
                $notification = str_replace(
                    [
                        $url,
                        'PHP Parse'
                    ],
                    [
                        $source,
                        'Raxon Parse'
                    ], $notification);
                $notification = explode('on line', $notification);
                if(array_key_exists(1, $notification)){
                    array_pop($notification);
                }
                $notification = implode('', $notification);
                //don't need $output
                throw new Exception($notification . PHP_EOL . 'Temp-file: ' . $url);
            }
            throw new Exception($output);
        }
    }
}