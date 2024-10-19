{{Raxon:Module:Core::uuid.variable()}}
{{$test = [
'This',
'is',
'a',
'test',
'test'
]}}
{{breakpoint($test)}}
{{array.asort($test, SORT_NATURAL)}}
{{dd($test)}}
/*
{{$array = ['beat','drum','base']}}
{{$search = 'base'}}
{{$x = array.binarysearch($array, $search)}}
{{d($string)}}
{{d($search)}}
{{breakpoint($x)}}
{{$array = ['base', 'beat','drum']}}
{{$search = 'drum'}}
{{$x = array.binarysearch($array, $search)}}
{{d($string)}}
{{d($search)}}
{{breakpoint($x)}}
*/
/*
{{system.autoload.prefix.add("Raxon:Module", config('framework.dir.module'))}}
Raxon:Module is default 'framework.dir.module' now
*/