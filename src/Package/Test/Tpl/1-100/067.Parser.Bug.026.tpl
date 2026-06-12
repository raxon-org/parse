{{$read_line =  'Video\'s'}}
{{$read_line|>string.replace:'\'':'\\\''}}
{{d($read_line)}}

