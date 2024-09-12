{{$string = config('project.dir.vendor')}}
{{$compare = '/Application/vendr/'}}
{{assert($string === $compare, 'Raxon\Exception\AssertException')}}