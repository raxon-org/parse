{{Raxon:Module:Core::uuid.variable()}}
{{$test.amazing = [
'This',
'is',
'a',
'x',
'test',
'test',
'test',
'test',
'test',
'test',
'test',
'test',
'test',
'test',
'test',
'test',
'test',
'test',
'test',
'test',
'test',
'test',
'test',
'test'
]}}
{{$test.original = $test.amazing}}
{{array.string.uppercase($test.amazing)}}
{{array.asort($test.amazing)}}
{{$keys = array.keys($test.amazing)}}
{{$test.amazing = array.values($test.amazing)}}
{{$index = array.binarysearch.record($test.amazing, 'A', $count)}}
{{d($keys[$index])}}
{{d($test.amazing[$index])}}
{{d($count)}}
{{$index = array.binarysearch.record($test.amazing, 'X', $count)}}
{{d($keys[$index])}}
{{d($test.amazing[$index])}}
{{d($count)}}
{{$index = array.binarysearch.record($test.amazing, 'THIS', $count)}}
{{d($keys[$index])}}
{{d($test.amazing[$index])}}
{{$testing = array.binarysearch.list($test.amazing, 'TEST', $count)}}
{{for.each($testing as $key => $value)}}
{{d($value + ' ' + $test.amazing[$value])}}
{{/for.each}}
{{d($count)}}