ready(() => {
    console.log('navigation init');
    desktop.init({
        "url": "{{literal}}{{route.get('application-desktop')}}{{/literal}}"
    });
});