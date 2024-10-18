{{$app = app()}}
{{$app->request('package')|default:'no-package'}}
{{$app->request('module')}}