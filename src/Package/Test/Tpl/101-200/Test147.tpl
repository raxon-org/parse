{{Raxon:Module:Core::uuid.variable()}}
{{$string = 'Hello World!'}}
{[$search = 'world'}}
{{$x = string.binarysearch.substring($string, $search)}}
{{d($string)}}
{{d($search)}}
{{breakpoint($x)}}

/*
{{system.autoload.prefix.add("Raxon:Module", config('framework.dir.module'))}}
Raxon:Module is default 'framework.dir.module' now
*/