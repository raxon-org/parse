{{$framework = self.framework()}}}
{{$data = self.data()}}
{{$parse = self.parse()}}
{{$flags = self.flags()}}
{{$options = self.options()}}
{{$config = $framework.config()}}
{{dd($config)}}
{{block.data()}}
{
    "string": "test",
    "float": 1.0001,
    "boolean": true,
    "array": [1, 2, 3, "0000{{$this.#key}}"],
    "object": {
        "string": "test2",
        "number": 1,
        "boolean": true,
        "boolean-false": "{{!$this.boolean}}",
        "array": [1, 2, 3],
        "parentNode": "{{$this.#parentNode.float|default:'no-parentNode'}}",
        "selector": "{{$this.array|object:Core::JSON}}",
        "uuid": "{{echo(Core::uuid())}}",
        "reference": "{{$this.uuid}}"
    },
    "function": "{{echo('test123')}}",
    "class": "System.Config",
    "options": {
        "parentProperty": "{{$this.#parentProperty}}",
        "parentProperty2": "{{$this.#parentNode.#property}}",
        "float": "{{$this.#parentNode.object.parentNode}}",
        "bool": "{{$this.#parentNode.object.boolean}}"
    },
    "attribute": "{{$this.#attribute}}",
    "property": "{{$this.#property}}",
    "parentProperty": "{{$this.#parentProperty}}",
    "parentNode": "{{$this.string|default:'no-parentNode'}}",
    "rootNode": "{{$this.#rootNode.function|default:'no-rootNode'}}",
    "key": "{{$this.#key|default:'no-key'}}",
    "url": "{{$this.#url|default:'no-url'}}",
    "null": null
}
{{/block}}