{{config('framework.version')}}

'{{config("framework.version")}}'
"{{config('framework.version')}}"
\"{{config('framework.version')}}\"

{{$test = "{{config('framework.version')}}"}}
{{$test}}

{{$test = '\\\'{{config(\'framework.version\')}}\''}}
{{$test}}

{{$test = "\"{{config('framework.version')}}\""}}
{{$test}}
/**
$test should return = \"..." now it is \\\"...\"
*/

test
