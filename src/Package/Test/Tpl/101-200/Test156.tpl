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
{{$search = array.binarysearch.list(
    $test,
    5,
    $count,
    Raxon:Module:Filter::OPERATOR.LOWER.THAN.EQUAL
)}}
{{d($search)}}

