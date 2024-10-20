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
{{$index = array.binarysearch.record($test.amazing, 'X', $count)}}
{{d($index)}}
{{breakpoint($count)}}