{{$string = config('project.dir.vendor')}}
{{assert($string === '/Application/vendr/', 'Raxon\Exception\AssertException')}}