<?php
$array = [  
  (object) [
    'role' => 'user',
    'content' => "{{json.encode(file.read('/mnt/Vps3/Mount/Shared/Plugin/Host/Host.Domain.php'))}}" 
  ]
];

$json = json_encode($array);

var_dump($array);
var_dump($json);
