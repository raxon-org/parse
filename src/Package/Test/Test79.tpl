{{block.data()}}
{
    "string": "test",
    "number": 1,
    "boolean": true,
    "array": [1, 2, 3],
    "object": {
        "string": "test",
        "number": 1,
        "boolean": true,
        "array": [1, 2, 3]
    }
    "function": "{{echo('test')}}",
    "class": "System.Config",
    "options": {

    },
    "assign": "{{literal}}{{$this.attribute|default:'no-attribute'}}{{/literal}}",
    "null": null
}
{{/block}}