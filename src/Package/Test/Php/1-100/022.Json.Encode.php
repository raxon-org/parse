<?php
$array = [  
  (object) [
    'role' => 'user',
    'content' => "{{json.encode(file.read('/mnt/Vps3/Mount/Shared/Plugin/Host/Host.Domain.php'))}}" 
  ]
];

var_dump($array);
