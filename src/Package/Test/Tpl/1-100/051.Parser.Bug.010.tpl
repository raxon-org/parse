{{$frontend.host = options('frontend.host')}}{{literal}}<?php

namespace Domain\{{/literal}}{{php.namespace.host($frontend.host)}}{{literal}}\Application\Filemanager\Service;

use Raxon\App;

class File
{

    public static function manager_tree_directory(App $object, array $options): string
    {
        switch ($options['type']) {
            case 'has_sub_dir' :
                return '
                <li class="' . $options['li']->class . '" data-url="' . $options['li']->data->url . '" data-frontend-url="' . $options['li']->data->frontend->url . '" data-target="' . $options['li']->data->target . '" data-method="' . $options['li']->data->method . '" data-dir="' . $options['li']->data->dir . '" data-indent="' . $options['li']->data->indent . '">
                    <p>
                        <span class="has-sub-dir expand"><i class="fas fa-angle-right"></i></span><span class="icon"><i class="far fa-folder"></i></span><span class="name">' . $options['node']->name . '</span><span class="loader"></span>
                    </p>
                </li>
                <section data-dir="' . $options['li']->data->dir . '">
                </section>
                ';
            default :
                return '
                <li class="' . $options['li']->class . '" data-url="' . $options['li']->data->url . '" data-frontend-url="' . $options['li']->data->frontend->url . '" data-dir="' . $options['li']->data->dir . '">
                    <p>
                        <span class="has-sub-dir">&nbsp;</span><span class="icon"><i class="far fa-folder"></i></span><span class="name">' . $options['node']->name . '</span><span class="loader"></span>
                    </p>
                </li>
                ';
        }
    }
}
{{/literal}}