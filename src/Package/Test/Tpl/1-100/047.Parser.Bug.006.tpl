{{$require.language = 'en'}}
{{$require.page = 'test_test'}}
{{$url =
config('controller.dir.data') +
'MarkDown' +
'/' +
$require.language +
'/' +
$request.page|string.replace:'_': '.' +
'.md'}}
{{dd($url)}}