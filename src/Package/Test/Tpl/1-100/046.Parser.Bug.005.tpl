{{$select = 'Universe Orange'}}
{{$select|>string.lowercase|>string.replace:'/',' '}}
/*
the comma ',' is placed wrong here, it should be a ':' character
{{$select|>string.lowercase|>string.replace:'/':' '}}
*/
