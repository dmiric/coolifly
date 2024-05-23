(function($, Drupal) {
    'use strict';

    $(document).ready(function () {

        // Create chat service
        var debug = true;
        var commentService = new WebsocketService({
            name: 'chat',
            debug: debug,
            onopen: function () {
            },
            onmessage: function (message) {
            },
            onerror: function (e) {
                if (debug) {
                    console.log('Websocket comments: ' + e.type);
                }
            },
            onclose: function (e) {
                if (debug) {
                    console.log('Websocket comments: ' + e.type);
                }
            }
        });
    });

})(jQuery, Drupal);
