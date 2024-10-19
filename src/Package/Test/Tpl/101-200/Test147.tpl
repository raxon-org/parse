{{Raxon:Module:Core::uuid.variable()}}
{{$test = [
'This',
'is',
'a',
'test',
'test'
]}}
{{array.asort($test, SORT_NATURAL)}}
{{$array = []}}
{{$index = []}}
{{for.each($test as $nr => $value)}}
{{$array[] = $value}}
{{$index[] = $nr}}
{{/for.each}}
{{$search = 'a'}}
{{$x = array.binarysearch($array, $search)}}
{{d($search)}}
{{breakpoint($x)}}
/*
{{system.autoload.prefix.add("Raxon:Module", config('framework.dir.module'))}}
Raxon:Module is default 'framework.dir.module' now
*/