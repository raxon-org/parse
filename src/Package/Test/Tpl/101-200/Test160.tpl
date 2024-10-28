### Hello world

```raxon
{{literal}}
{{html.image('https://raxon.org/Index/Image/Performance.png', 'Performance')}}
{{/literal}}
```

![Alt text](https://raxon.org/Index/Image/Performance.png "Performance")


```raxon
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
{{$offset = 5}}
{{$search = array.binarysearch.list(
    $test,
    5,
    Raxon:Module:Filter::OPERATOR.LOWER.THAN.EQUAL,
    $count,
    $limit,
    $offset
)}}
{{d($search)}}
```

/**
prompt:
convert to plain markdown: "{{file.read('/mnt/Vps3/Mount/Package/Raxon/Parse/Test/Tpl/101-200/Test160.tpl')}}"
*/

