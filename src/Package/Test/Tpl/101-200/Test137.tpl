{{$app = app()}}
{{$app->request('module2')|default:'module2'}}