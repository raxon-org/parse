{{$module = 'test'}}
{{$right = "<a href=\"{{server.url('docs.raxon.org')}}Mod\"ule/{{$module|>string.replace:'\\':'/'}}\">{{$module}}</a>"}}        
{{$module}}
{{$right}}