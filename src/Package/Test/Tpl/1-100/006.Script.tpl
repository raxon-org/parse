{{script('module', $variable)}}


...content
{{require(config('controller.dir.view') + 'User' + '/Module/Authorization.js')}}

{{/script}}
test
{{dd('$this')}}