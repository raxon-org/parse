{{$framework = framework()}}
{{$app = 1000}}
{{$time.duration = $app - ($framework.config('time.start') * 1000) + 'ms'}}
{{dd($time)}}