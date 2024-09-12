{{$string = config('project.dir.vendor')}}
{{$compare = '/Application/vendor/'}}
{{assert($string === $compare)}}