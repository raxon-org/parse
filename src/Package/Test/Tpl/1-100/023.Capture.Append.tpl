{{capture.append('capture')}}
...content
{{require(config('controller.dir.view') + 'User' + '/Module/Authorization.js')}}
{{/capture.append}}
{{dd('{{this}}')}}