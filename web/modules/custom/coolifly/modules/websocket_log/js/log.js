(function($, Drupal) {
    'use strict';

    $(document).ready(function () {

        // Create log
        var logUi = (function () {
            return {
                thread: $('.log-messages'),
                initedOnce: false,
                init: function () {
                    // Do only once. Not on reconnect
                    if (!this.initedOnce) {
                        this.initedOnce = true;
                        // Set event
                       logService.send("Log Start");
                    }
                },
                appendMessage: function (message) {
                    this.thread.append('<p>' + message + '</p>');
                }
            }
        })();

        // Create log service
        var debug = true;
        var logService = new WebsocketService({
            name: 'log',
            debug: debug,
            onopen: function () {
                logUi.init();
            },
            onmessage: function (message) {
                logUi.appendMessage(message);
            },
            onerror: function (e) {
                if (debug) {
                    console.log('Websocket log: ' + e.type);
                }
            },
            onclose: function (e) {
                if (debug) {
                    console.log('Websocket log: ' + e.type);
                }
            }
        });
    });

})(jQuery, Drupal);
