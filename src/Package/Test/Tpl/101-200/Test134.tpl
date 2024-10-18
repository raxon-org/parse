{{$app = app()}}
{{breakpoint($app::request('package'))}}
{{breakpoint($app::request('module'))}}
{{breakpoint($app::request())}}