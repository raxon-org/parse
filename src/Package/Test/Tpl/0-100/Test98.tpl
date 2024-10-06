{{$array1 = (object) [
'test1',
'test4'
]}}
{{$array2 = (object) [
'test3',
'test2'
]}}
{{$array = $array1 + $array2}}
{{dd($array)}}
