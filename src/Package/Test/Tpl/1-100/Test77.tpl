{
    "string": "test",
    "number": 1,
    "boolean": true,
    "float": 3.0003,
    "array": [1, 2, 3],
    "object": {
        "string": "test",
        "number": 1,
        "float": 1.0001,
        "boolean": false,
        "array": [1, 2, 3]
    },
    "function": "{{echo('test')}}",
    "class": "System.Config",
    "options": {

    },
    "trait": "{{Raxon.Node:Data:list($this.class,Raxon.Node:Role:role_system(), $this.options)}}",
    "assign": "{{$this.attribute|default:'no-attribute'}}",
    "null": null
}