{{$response = Package.Raxon.Parse:Main:compile(flags(), options())}}
{{if(
is.array($response) ||
is.object($response)
)}}
{{$response|json.encode}}
{{else}}
{{$response|default:''}}
{{/if}}
