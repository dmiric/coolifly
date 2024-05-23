Real-time Drupal powered by Ratchet.

# INSTALLATION

Preferred way to download a module with composer.
Please run 'composer require drupal/websocket'
from the webroot directory (neither inside the core directory nor
inside websocket module directory).

# USAGE

Module adds drush command 'start-websocket-server' to start websocket
server powered by Ratchet library. Basically, main module provides only
basic structures to build websocket services, which can be provided by
submodules or other modules.

There are two general ways to run a websocket server:

1. Start as local service (on localhost) on some high port and then expose over
a reverse proxy (recommended)
2. Start as world wide service on some high port directly (fine for testing
purposes)

## Reverse proxy mode

To start websocket server as local service for a reverse proxy use:

```
drush startsocket
```
Or if you don't run your web server over SSL:
```
drush startsocket --no-ssl
```

These commands will start a websocket server at ```ws://localhost:3000```

However, Drupal will expect websocket connections at
```wss://your.domain/websocket``` (or ```ws://```).

To access them you will need to amend your web server configuration first, and
expose local service at above URLs.

Use ```--just-print``` option to see necessary configuration.

### Nginx reverse proxy configuration

/etc/nginx/sites-enabled/your.domain.conf:
```
upstream websocket {
  server localhost:3000;
}

server {
    listen 80;
    #listen 443 ssl;
    ...

    location /websocket {
        proxy_pass http://websocket;
        proxy_http_version 1.1;
        proxy_set_header Upgrade websocket;
        proxy_set_header Connection upgrade;
        proxy_set_header Host localhost;
    }
    ...
}
```

### Apache2 reverse proxy configuration

/etc/apache2/sites-enabled/your.domain.conf:
```
<Proxy ws://localhost:3000/*>
  Allow from all
</Proxy>
<VirtualHost *:80>
#<VirtualHost *:443>
  ...
  <LocationMatch "/websocket">
    ProxyPass ws://localhost:3000/websocket
    ProxyPassReverse ws://localhost:3000/websocket
  </LocationMatch>
  ...
</VirtualHost>

```

Be sure to enable all necessary Apache2 modules:
 * ```a2enmod proxy```
 * ```a2enmod proxy_http```
 * ```a2enmod proxy_wstunnel```

This way you will be able to access websocket on normal HTTP port:
```
ws:///your.domain/websocket
wss:///your.domain/websocket
```

## Normal mode

You can also start a websocket server in a non-reverse proxy mode.
Thus means that this service will pass other firewall rules since it uses other
world-wide port instead of standard HTTP ports.

```
drush startsocket --no-reverse-proxy
```
Or to specify port number:
```
drush startsocket 3000 --no-reverse-proxy
```

Websocket server will start at:
```
ws://your.domain:3000
```

Websocket server in normal mode will always run without SSL support,
since SSL is only provided by proxying web server.

# SUBMODULES

## Websocket chat

Chat submodule will enable a simple message hub over websocket at:
```wss://your.domain/websocket/chat```

It provides a Drupal block with simple chat.

## Websocket (real-time) comments

TODO

# SEE OTHER

More information about Ratchet: http://socketo.me/

For deployment please refer to http://socketo.me/docs/deploy.
