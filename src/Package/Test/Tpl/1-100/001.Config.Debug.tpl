{{$config = config('project.dir.vendor')}}
{{if($config === 'test')}}
test
{{elseif($config === '/Application/vendor/')}}
found
{{/if}}
{{d($config)}}
