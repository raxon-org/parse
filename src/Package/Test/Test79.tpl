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
        "array": [1, 2, 3],
        "parentNode": "{{$this.#parentNode.number|default:'no-parentNode'}}"
    },
    "function": "{{echo('test123')}}",
    "class": "System.Config",
    "options": {

    },
    "assign": "{{$this.#attribute|default:'no-attribute'}}",
    "parentNode": "{{$this.#parentNode.string|default:'no-parentNode'}}",
    "rootNode": "{{$this.#rootNode.function|default:'no-rootNode'}}",
    "null": null
}
{{/block}}