{{capture.append($script)}}
...content
{{require(config('controller.dir.view') + 'User' + '/Module/Authorization.js')}}
{{/capture.append}}
hello
{{d('$this')}}