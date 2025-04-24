{{$id = 1}}
{{$li.data.dir = 'example'}}
{{$li.data.target = "section[id='" + $id + "'] ul.tree section[data-dir='" + $li.data.dir + "']"}}
{{$li.data.target}}
