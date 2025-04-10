{{capture.append('script')}}
...content
{{require(config('controller.dir.view') + 'User' + '/Module/Authorization.js')}}
{{/capture.append}}
{{capture.prepend('script')}}
...before
{{/capture.prepend}}
hello
{{d('$this')}}