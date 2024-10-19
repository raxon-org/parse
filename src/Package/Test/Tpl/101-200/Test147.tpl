{{Raxon:Module:Core::uuid.variable()}}
{{$string = '
In the second example code above, we convert both strings to lowercase (or uppercase, if you prefer) before making the comparison. The substr function is then used to extract a portion of the original string ($arr) starting at position $mid. This extracted substring has a length equal to that of $x, which allows us to compare it directly with the search term ($x).

'}}
{{$search = '($x)'}}
{{$x = string.binarysearch.substring($string, $search)}}
{{d($string)}}
{{d($search)}}
{{breakpoint($x)}}

/*
{{system.autoload.prefix.add("Raxon:Module", config('framework.dir.module'))}}
Raxon:Module is default 'framework.dir.module' now
*/