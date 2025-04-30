{{$zero = 0}}
{{$execute =[
$zero => (object) [
    $zero => (object) [
        'test'
    ],
'test'
]
]}}
{{config('test', $execute[$zero][$zero][$zero])}}
{{config('test')}}
