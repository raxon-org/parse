{{block.data()}}
{
    "string": "test",
    "float": 1.0001,
    "boolean": true,
    "array": [1, 2, 3, "0000{{$this.#key}}"],
    "object": {
        "boolean": true,
        "boolean-false": "{{!$this.boolean}}",
        "three": {
            "bool": "{{!$this.#parentNode.boolean}}",
            "bool2": "{{!!!!$this.#parentNode.boolean}}",
            "int": "{{(integer) $this.#rootNode.float}}"
        },
        "four": "{{(clone) $this.#rootNode.object.three}}"
    }
}
{{/block}}