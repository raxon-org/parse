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
{{$limit = 5}}
{{while((bool) array.binarysearch.record($test, 3, $count, Raxon:Module:Filter::operator.smaller.equal('<='), $search, $limit, true))}}{{/while}}
{{d($search)}}

