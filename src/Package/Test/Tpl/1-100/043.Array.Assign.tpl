{{$one = '1'}}
{{$two = '2'}}
{{$array = []}}
{{$array[$one] = []}}
{{$array[$one][$two] = 'test'}}
{{dd($array)}}