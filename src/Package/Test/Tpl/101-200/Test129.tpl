{{$j = 0}}
{{for ($i = 0, $j = 10, $q=2; $i < 5; $i++, $j+=2, $q*=2)}}
{{echo
    ('i: ' + $i + ', j: ' + $j + ', q: ' + $q + "\n")}}
{{/for}}