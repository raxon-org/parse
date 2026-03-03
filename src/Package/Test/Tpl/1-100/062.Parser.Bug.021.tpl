{{$exception.location = []}}
{{$exception.location[] = 'test'}}

{{if(
(
!is.empty($exception.location) &&
is.array($exception.location)
) &&
(
config('framework.environment')
) === 'development'

)}}
############################## YES
{{/if}}