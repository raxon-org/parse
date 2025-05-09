{{$require.language = 'en'}}
{{$request.page = 'test_test'}}
{{$require.page = $request.page|string.replace:'_': '.'}}
{{$url =
config('controller.dir.data') +
'MarkDown' +
'/' +
$require.language +
'/' +
$require.page +
'.md'}}
{{dd($url)}}