{{$one = '1'}}
{{$two = '2'}}
{{$array = []}}
{{$array[3] = []}}
{{$array[3][2] = 'test'}}
{{$array[3][5] = 'test2'}}

{{d($array)}}