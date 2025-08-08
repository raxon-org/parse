{{$module = 'test'}}
{{$right = "<a href=\"{{server.url('docs.raxon.org')}}/Module/{{$module|>string.replace:'\\':'/'}}\">{{$module}}</a>"}}        
{{dd($right)}}