{{Raxon:Module:Core::uuid.variable()}}
{{$test.amazing = [
'This',
'is',
'a',
'test',
'test'
]}}
{{array.string.lowercase($test.amazing)}}
{{array.string.uppercase($test.amazing)}}
{{array.asort($test.amazing, SORT_NATURAL)}}
{{$array.amazing = []}}
{{$index.amazing = []}}
{{for.each($test.amazing as $nr => $value)}}
{{$array.amazing[] = $value}}
{{$index.amazing[] = $nr}}
{{/for.each}}
{{$search = 'TEST'}}
{{$x = array.binarysearch($array.amazing, $search)}}
{{d($x)}}
{{for.each($x as $nr => $key.key.doubt)}}
{{d($array.amazing[$key.key.doubt])}}
{{d($index.amazing[$key.key.doubt])}}
{{/for.each}}
{{d($index)}}
/*
{{system.autoload.prefix.add("Raxon:Module", config('framework.dir.module'))}}
Raxon:Module is default 'framework.dir.module' now
*/