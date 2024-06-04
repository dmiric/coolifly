(function($, Drupal) {
  'use strict';

  $(function() {

    const terminal = new Terminal();
    terminal.open(document.getElementById('terminal_desc'));

    // const socket = new WebSocket('https://coolifly.ddev.site/ws');

    const socket = io(
      'wss://coolifly.ddev.site',
      {
         path :'/socket.io',
         transports: ['websocket'],
         autoConnect: false
      });

    socket.auth = { username: 'djuro' };
    socket.connect();

    socket.onAny((event, ...args) => {
      console.log(event, args);
    });

    socket.on("connect", () => {
      console.log(socket.id); // x8WIv7-mJelg7on_ALbx
      terminal.write('Connected to server\r\n');
      terminal.write(socket.auth.username + 'Connected to server\r\n');
    });

    socket.on("connect_error", (err) => {
      if (err.message === "invalid username") {
        this.usernameAlreadySelected = false;
      }
    });

    socket.on("session", ({ sessionID, userID }) => {
      // attach the session ID to the next reconnection attempts
      socket.auth = { sessionID };
      // store it in the localStorage
      localStorage.setItem("sessionID", sessionID);
      // save the ID of the user
      socket.userID = userID;
      terminal.write(socket.userID + '\r\n');
    });

    socket.on("private message", ({ content, from, to }) => {
      console.log(content);
      terminal.write(content);
      for (let i = 0; i < this.users.length; i++) {
        const user = this.users[i];
        const fromSelf = socket.userID === from;
        if (user.userID === (fromSelf ? to : from)) {
          user.messages.push({
            content,
            fromSelf,
          });
          if (user !== this.selectedUser) {
            user.hasNewMessages = true;
          }
          break;
        }
      }
    });

    socket.on("disconnect", () => {
      console.log(socket.connected); // false
    });

  });

})(jQuery, Drupal);
