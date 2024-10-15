{{$j = 0}}
{{for($i = 0; $i < 5; $i++)}}
{{echo('i: ' + $i + "\n")}}
{{/for}}
/*
"variable": {
  "is_assign": true,
  "operator": "=",
  "name": "j",
  "value": {
    "string": "0",
    "array": [
        {
            "type": "integer",
            "value": "0",
            "execute": 0
        }
    ]
  }
}
*/