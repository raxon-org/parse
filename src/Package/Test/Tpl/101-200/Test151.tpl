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
{{d($keys)}}
{{d($test.amazing)}}
{{$index = array.binarysearch.list($test.amazing, 'A', $count)}}
{{d($index)}}
{{d($count)}}
{{$index = array.binarysearch.list($test.amazing, 'X', $count)}}
{{d($index)}}
{{d($count)}}
{{$index = array.binarysearch.list($test.amazing, 'THIS', $count)}}
{{d($index)}}
{{d($count)}}