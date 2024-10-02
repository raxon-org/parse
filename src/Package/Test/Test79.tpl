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
        "parentNode": "{{$this.#parentNode|default:'no-parentNode'}}"
    },
    "function": "{{echo('test')}}",
    "class": "System.Config",
    "options": {

    },
    "assign": "{{$this.#attribute|default:'no-attribute'}}",
    "parentNode": "{{$this.#parentNode|default:'no-parentNode'}}",
    "rootNode": "{{$this.#rootNode.string|default:'no-parentNode'}}",
    "null": null
}
{{/block}}