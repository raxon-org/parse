{{$test = 'test'}}
{{$comparison.1 = '/Application/vendor/'}}
{{$comparison.2 = 'test'}}

{{if(config('project.dir.vendor') === $comparison.1)}}
ok1
{{if($test === $comparison.2)}}
test
{{else}}
ok2
{{/if}}
{{/if}}
