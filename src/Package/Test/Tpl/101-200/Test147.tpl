{{Raxon:Module:Core::uuid.variable()}}
{{$test = [
'This',
'is',
'a',
'test',
'test'
]}}
{{array.string.lowercase($test)}}
{{array.string.uppercase($test)}}
{{array.asort($test, SORT_NATURAL)}}
{{$array = []}}
{{$index = []}}
{{for.each($test as $nr => $value)}}
{{$array[] = $value}}
{{$index[] = $nr}}
{{/for.each}}
{{$search = 'TEST'}}
{{$x = array.binarysearch($array, $search)}}
{{breakpoint($x)}}
{{for.each($x as $nr => $key)}}
{{d($array[$key])}}
{{d($key)}}
{{/for.each}}
{{d($index)}}
/*
{{system.autoload.prefix.add("Raxon:Module", config('framework.dir.module'))}}
Raxon:Module is default 'framework.dir.module' now
*/