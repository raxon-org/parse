{{capture.append('script')}}
...content
{{require(config('controller.dir.view') + 'User' + '/Module/Authorization.js')}}
{{/capture.append}}
{{capture.prepend('script')}}
...before
{{capture.prepend('script')}}
...before in script
{{/capture.prepend}}
{{/capture.prepend}}
hello
{{d('$this')}}