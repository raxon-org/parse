{{$test = 'test' + 1}}
{{$comparison.1 = '/Application/vendor/'}}
{{$comparison.2 = 'test1'}}

{{if(config('project.dir.vendor') === $comparison.1)}}
{{$test4}}
{{/if}}
