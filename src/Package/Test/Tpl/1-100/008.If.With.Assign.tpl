{{$test = 'test' + 1}}
{{$comparison.1 = '/Application/vendor/'}}
{{$comparison.2 = 'test1'}}

{{if(config('project.dir.vendor') === $comparison.2)}}
no
{{elseif(config('project.dir.vendor') === $comparison.1)}}
{{$test2 = 'test' + 2}}
{{$test2}}
{{if($test === $comparison.2)}}
{{$test3 = 'test' + 3}}
{{$test3}}
{{else}}
ok2
{{/if}}
{{/if}}