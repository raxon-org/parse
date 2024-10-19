{{Raxon:Module:Core::uuid.variable()}}
{{$array = [3,5,7]}}
{{$search = 5}}
{{$x = array.binarysearch($array, $search)}}
{{d($string)}}
{{d($search)}}
{{breakpoint($x)}}

/*
{{system.autoload.prefix.add("Raxon:Module", config('framework.dir.module'))}}
Raxon:Module is default 'framework.dir.module' now
*/