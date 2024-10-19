{{breakpoint(config('framework.dir'))}}

{{system.autoload.add({
"prefix": "Raxon:Module",
"directory": "{{config('project.dir.plugin')}}",
"extension": ""
})}}
{{Raxon:Module:Core2::uuid.variable()}}