// Websocket service store singleton object
var WebsocketStore = (function ($) {

    return {
        // Services store
        services: {},

        // Register websocket service for later connection
        register: function (serviceSettings) {

            // Service name is obligatory
            if (serviceSettings.name === undefined) {
                throw 'Websocket service: Service name is not provided';
            }

            // Do not try to register services with same name
            if (this.get(serviceSettings.name)) {
                throw 'Websocket service (' + serviceSettings.name + '): Already registered';
            }

            // Get settings and go on with registration
            var store = this;
            var settingsPath = '/ws/settings/' + serviceSettings.name + '?debug=' + serviceSettings.debug;
            $.ajax({
                url: settingsPath,
                // Register service on success
                success: function (data, status, xhr) {
                    store.onregister(serviceSettings, data);
                },
                // Handle getting settings error
                error: function (xhr, status, msg) {
                    // Decide about error message
                    var eventName = 'WebsocketUnexpectedError: ' + msg;
                    switch (xhr.status) {
                        // Not allowed
                        case 403:
                            eventName = 'WebsocketNotAllowedError';
                            // Log for debugging
                            if (serviceSettings.debug) {
                                console.log('Websocket service (' + serviceSettings.name + '): Not allowed to get service settings at ' + settingsPath);
                            }
                            break;
                        // Not found
                        case 404:
                            eventName = 'WebsocketNotFoundSettingsError';
                            // Log for debugging
                            if (serviceSettings.debug) {
                                console.log('Websocket service (' + serviceSettings.name + '): Could not find service settings at ' + settingsPath);
                            }
                            break;
                        // Not available
                        case 502:
                            eventName = 'WebsocketNotAvailableError';
                            // Log for debugging
                            if (serviceSettings.debug) {
                                console.log('Websocket service (' + serviceSettings.name + '): Service is not running');
                            }
                            break;
                        default:
                    }
                    // Fire error event
                    store.onerror(serviceSettings, new Event(eventName));
                }
            });
        },

        // Remove registration
        unregister: function (serviceSettings) {
            this.set(serviceSettings.name, undefined);
        },

        // Connect to websocket
        onregister: function (serviceSettings, connectionSettings) {
            try {
                var conn = new WebSocket(connectionSettings.path);
            }
            catch (e) {
                this.onerror(serviceSettings, new Event('WebsocketConnectionError'));
                return;
            }

            this.set(serviceSettings.name, conn);

            var store = this;
            conn.onopen = function(event) {
                store.onopen(serviceSettings, event);
            };

            conn.onclose = function (event) {
                store.onclose(serviceSettings, event);
            };

            conn.onerror = function (event) {
                store.onerror(serviceSettings, event);
            };

            conn.onmessage = function(event) {
                store.onmessage(serviceSettings, event.data, event);
            };
        },

        // Open connection event handler
        onopen: function (serviceSettings, event) {
            if (serviceSettings.debug) {
                console.log('Websocket service (' + serviceSettings.name + '): Connection established');
            }
            if (serviceSettings.onopen !== undefined) {
                serviceSettings.onopen(event);
            }
        },

        // Close connection event handler
        onclose: function (serviceSettings, event) {
            if (serviceSettings.debug) {
                console.log('Websocket service (' + serviceSettings.name + '): Connection closed');
            }
            this.unregister(serviceSettings);
            if (serviceSettings.onclose !== undefined) {
                serviceSettings.onclose(event);
            }
            var store = this;
            this.delay(function(){
                store.reconnect(serviceSettings);
            }, 5000 );
        },

        // Close connection event handler
        onerror: function (serviceSettings, event) {
            if (serviceSettings.debug) {
                console.log('Websocket service (' + serviceSettings.name + '): Connection error');
            }
            if (serviceSettings.onerror !== undefined) {
                serviceSettings.onerror(event);
            }
            var store = this;
            this.delay(function(){
                store.reconnect(serviceSettings);
            }, 5000 );
        },

        // Message event handler
        onmessage: function (serviceSettings, message, event) {
            if (serviceSettings.debug) {
                console.log('Websocket service (' + serviceSettings.name + '): Message arrived = ' + message);
            }
            if (serviceSettings.onmessage !== undefined) {
                serviceSettings.onmessage(message, event);
            }
        },

        // Get service
        get: function (serviceName) {
            if (this.services[serviceName] === undefined) {
                return null;
            }
            return this.services[serviceName];
        },

        // Set / add service
        set: function (serviceName, service) {
            this.services[serviceName] = service;
        },

        // Delay callback based function
        delay: (function() {
            var timer = 0;
            return function(callback, ms) {
                clearTimeout (timer);
                timer = setTimeout(callback, ms);
            };
        })(),

        // Reconnect
        reconnect: function (serviceSettings) {
            if (serviceSettings.debug) {
                console.log('Websocket service (' + serviceSettings.name + '): Reconnecting');
            }
            this.unregister(serviceSettings);
            this.register(serviceSettings);
        }
    };
})(jQuery);

// class WebsocketService (proxy class)
WebsocketService = (function (serviceSettings) {

    // Register service
    WebsocketStore.register(serviceSettings);

    return {
        // Reference to services store
        store: WebsocketStore,

        // Service name
        name: serviceSettings.name,

        // Send message proxy function
        send: function (message) {
            this.store.get(this.name).send(message);
        }
    };
});