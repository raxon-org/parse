{{breakpoint(config('framework.dir'))}}
{{system.autoload.add("Raxon:Module", config('project.dir.plugin'))}}
{{Raxon:Module:Core2::uuid.variable()}}