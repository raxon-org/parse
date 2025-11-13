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
namespace Plugin;

use Raxon\Module\File;

trait Source_Type {

    protected function source_type(string $url=''): string
    {
        $options = [];
        $options['url'] = $url;
        if(!empty($options['url'])){
            $url = $options['url'];
            $extension = File::extension($url);
            switch($extension){
                case 'wav' :
                    return 'audio/wav';
                case 'ogg' :
                    return 'audio/ogg';
                case 'mp3' :
                    return 'audio/mp3';
                case 'png' :
                    return 'image/png';
                case 'jpg' :
                    return 'image/jpeg';
                case 'bmp' :
                    return 'image/bmp';
                case 'gif' :
                    return 'image/gif';
                case 'webp' :
                    return 'image/webp';
                case 'mp4':
                    return 'video/mp4';
                default:
                    return '';
            }
        } else {
            return '';
        }
    }

}