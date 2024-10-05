{{$framework.test = framework()}}
{{$app = 1000}}
{{$time.duration = $app - ($framework.test::config('time.start') * 1000) + 'ms'}}
{{dd($time)}}