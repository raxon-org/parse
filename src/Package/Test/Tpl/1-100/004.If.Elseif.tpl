{{$test = 'test'}}
{{$comparison.1 = 'ok'}}
{{$comparison.2 = 'test'}}

{{if($test === $comparison.1)}}
ok1
{{elseif($test === $comparison.2)}}
test
{{else}}
ok2
{{/if}}
