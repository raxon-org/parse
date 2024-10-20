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
{{breakpoint($test.amazing)}}
{{$index = array.binarysearch.list($test.amazing, 'A', $count)}}
{{d($index)}}
{{$index = array.binarysearch.list($test.amazing, 'a', $count)}}
{{d($index)}}
{{d($count)}}