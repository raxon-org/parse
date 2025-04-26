{{$response = Package.Raxon.Parse:Main:compile(flags(), options())}}
{{if(
is.array($response) ||
is.object($response)
)}}
{{$response|json.encode:JSON_PRETTY_PRINT}}
{{else}}{{$response|default:''}}{{/if}}
