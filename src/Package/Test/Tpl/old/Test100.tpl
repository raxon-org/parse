{{$array1 = (object) [
'test1' => 1,
'test4' => 2
]}}
{{$array2 = [
'test3' => 3,
'test2' => 4
]}}
{{$array = $array1 + $array2}}
test1 {{breakpoint($array)}}
test2/*test3*/
/*
comment

*/
{{/* ... */}}
{{/*
test
{{$test = true}}
{{$test}}
*/}}
test3 //dit is een comment
{{ //dit is ook een comment
$test3|default:'test3'}}
{{ //dit is ook een comment2}}