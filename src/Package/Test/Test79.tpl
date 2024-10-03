{{block.data()}}
{
    "string": "test",
    "float": 1.0001,
    "boolean": true,
    "array": [1, 2, 3, "0000{{$this.#key}}"],
    "object": {
        "string": "test",
        "number": 1,
        "boolean": true,
        "array": [1, 2, 3],
        "parentNode": "{{$this.#parentNode.float|default:'no-parentNode'}}",
        "selector": "{{$this.boolean}}"
    },
    "function": "{{echo('test123')}}",
    "class": "System.Config",
    "property": "{{$this.#property}}",
    "options": {
        "parentProperty": "{{$this.#parentProperty}}",
        "parentNodeProperty": "{{$this.#parentNode.property}}"
    },
    "attribute": "{{$this.#attribute}}",

    "parentNode": "{{$this.#parentNode.string|default:'no-parentNode'}}",
    "rootNode": "{{$this.#rootNode.function|default:'no-rootNode'}}",
    "key": "{{$this.#key|default:'no-key'}}",
    "url": "{{$this.#url|default:'no-url'}}",
    "null": null
}
{{/block}}