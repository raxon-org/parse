{{$response = Package.Raxon.Parse:Main:compile(flags(), options())}}
{{if(!is.scalar($response))}}
{{$response|json.encode}}
{{else}}
{{$response}}
{{/if}}