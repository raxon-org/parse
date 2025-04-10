{{capture.append('script')}}
...content
{{require(config('controller.dir.view') + 'User' + '/Module/Authorization.js')}}
{{/capture.append}}
{{capture.prepend('script')}}
...before in script 1
{{capture.prepend('script')}}
...before in script 2
{{/capture.prepend}}
{{capture.prepend('script')}}
...before in script 3
{{/capture.prepend}}
{{/capture.prepend}}
hello
{{d('$this')}}