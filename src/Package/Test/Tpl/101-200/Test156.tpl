{{$test = [
0,
1,
2,
3,
3,
3,
3,
3,
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
{{array.binarysearch.list(
    $test,
    3,
    $count,
    Raxon:Module:Filter::OPERATOR.EQUAL
)}}
{{d($search)}}

