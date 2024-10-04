<?php
$data = (object) [];
$begedoip_colp_kdmd_aikn_cddnoikpbgci = $data->get('framework');
try {
    $methods = get_class_methods($begedoip_colp_kdmd_aikn_cddnoikpbgci);
    if(!in_array('config', $methods, true)){
        throw new TemplateException('Method "config" not found in: framework.config');
    }
catch(Exception $exception){
        throw new TemplateException('{{$config = $framework.config()}}
On line: 2, column: 1 in source: /mnt/Vps3/Mount/Package/Raxon/Parse/Test/Test80.tpl.', 0, $exception);
    }
$data->set('config', $begedoip_colp_kdmd_aikn_cddnoikpbgci->config());