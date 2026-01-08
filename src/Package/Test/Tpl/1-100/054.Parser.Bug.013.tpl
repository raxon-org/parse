ready(() => {
    console.log('navigation init');
    desktop.init({
        "url": "{{literal}}{{route.get('application-desktop')}}{{/literal}}"
    });
    application.init({
        "url": "{{literal}}{{server.url({{/literal}}{{options('backend.host')}}{{literal}})}}{{/literal}}Node/Application"
    });
    keyboard.init({
        "navigation": navigation,
        "application": application
    });
});