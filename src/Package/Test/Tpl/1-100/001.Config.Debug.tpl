{{$config = config('project.dir.vendor')}}
{{if($config === 'test')}}
test
{{elseif($config === '/Application/Vendor')}}
found
{{/if}}
{{d($config)}}
