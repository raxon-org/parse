{{$framework.test = framework()}}
{{$app = ::Raxon::App::instance()}}
{{$time.duration = ($app->config('time.start') - $framework.test->config('time.start')) * 1000 + ' ms'}}
{{$instance = App::instance()}}
{{$time.instance = ($instance->config('time.start') - $framework.test->config('time.start')) * 1000 + ' ms'}}
{{breakpoint($time)}}