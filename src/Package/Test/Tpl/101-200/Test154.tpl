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
{{$test2 = array.binarysearch.record($test, 10, $count, Raxon:Module:Filter::OPERATOR.EQUAL)}}
{{d($count)}}
{{d($test)}}
{{d($test2)}}

