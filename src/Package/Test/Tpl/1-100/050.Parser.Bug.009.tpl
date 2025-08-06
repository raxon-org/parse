{{$backend.host = options('backend.host')}}{{literal}}
//{{RAX}}
import { address } from "/Application/Filemanager/Module/Address.js";
import { directory } from "/Application/Filemanager/Module/Directory.js";
import { file } from "/Application/Filemanager/Module/File.js";
import { head } from "/Application/Filemanager/Module/Head.js";
import { menu } from "/Application/Filemanager/Module/Menu.js";
import { root } from "/Module/Web.js";
import { taskbar } from "/Application/Desktop/Module/Taskbar.js";
import { task } from "/Application/Desktop/Module/Task.js";
import { dialog } from "/Dialog/Module/Dialog.js";
import { language, translation } from "/Module/Translation.js";
import { version } from "/Module/Priya.js";
import user from "/Module/User.js";
require(
    [
        root() + 'Application/Filemanager/Css/Indent.css?' + version(),
        root() + 'Application/Filemanager/Css/Filemanager.css?' + version(),
        root() + 'Dialog/Css/Dialog.css?' + version(),
        root() + 'Js/Dropzone/5.9.2/min/dropzone.min.css?' + version(),
        root() + 'Js/Dropzone/5.9.2/min/dropzone.min.js?' + version()
    ],
    function(){
        user.refreshUrl("{{server.url('{{/literal}}{{$backend.host}}