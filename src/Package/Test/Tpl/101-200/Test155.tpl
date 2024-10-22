{{$test = [
0,
1,
2,
3,
4,
5,
6,
7,
8,
9,
10
]}}
{{$count = array.count($test)}}
{{$test2 = array.binarysearch.record($test, 1, $count, Raxon:Module:Filter::operator.smaller.equal('<='), $search)}}
{{d($test2)}}
{{$test2 = array.binarysearch.record($test, 1, $count, Raxon:Module:Filter::operator.smaller.equal('<='), $search)}}
{{d($test2)}}
{{$test2 = array.binarysearch.record($test, 1, $count, Raxon:Module:Filter::operator.smaller.equal('<='), $search)}}
{{d($test2)}}
{{d($count)}}
{{d($test)}}
{{d($test2)}}
{{d($search)}}

