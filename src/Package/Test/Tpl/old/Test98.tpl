{{$array1 = (object) [
'test1' => 1,
'test4' => 2
]}}
{{$array2 = (object) [
'test3' => 3,
'test2' => 4
]}}
{{$array = $array1 + $array2}}
{{dd($array)}}
