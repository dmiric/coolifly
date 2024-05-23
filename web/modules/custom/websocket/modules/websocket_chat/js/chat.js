(function($, Drupal) {
    'use strict';

    $(document).ready(function () {

        // Create chat interface
        var chatUi = (function () {
            return {
                form: $('form[data-drupal-selector="chat-form"]'),
                msgInput: $('textarea[data-drupal-selector="edit-chat-message"]'),
                submitBtn: $('input[data-drupal-selector="chat-form-submit"]'),
                thread: $('.chat-messages'),
                initedOnce: false,
                initForm: function () {
                    // Enable form
                    this.enableForm();

                    // Do only once. Not on reconnect
                    if (!this.initedOnce) {
                        this.initedOnce = true;
                        // Set event
                        var chatUiPass = this;
                        this.form.on('submit', this.form, function(e) {
                            e.preventDefault();
                            var message = chatUiPass.getMessage();
                            if (message !== '') {
                                chatService.send(message);
                            }
                        });
                    }
                },
                enableForm: function () {
                    $('.form-type-textarea').removeClass('form-disabled');
                    this.msgInput.removeAttr('disabled');
                    this.msgInput.removeAttr('placeholder');
                    this.submitBtn.removeAttr('disabled');
                },
                disableForm: function () {
                    $('.form-type-textarea').addClass('form-disabled');
                    this.msgInput.attr('disabled', 'disabled');
                    this.msgInput.attr('placeholder', Drupal.t('Connecting...'));
                    this.submitBtn.attr('disabled', 'disabled');
                },
                getMessage: function () {
                    var message = this.msgInput.val();
                    this.msgInput.val('');
                    return message;
                },
                appendMessage: function (message) {
                    this.thread.append('<p>' + message + '</p>');
                }
            }
        })();

        // Create chat service
        var debug = true;
        var chatService = new WebsocketService({
            name: 'chat',
            debug: debug,
            onopen: function () {
                chatUi.initForm();
            },
            onmessage: function (message) {
                chatUi.appendMessage(message);
            },
            onerror: function (e) {
                if (debug) {
                    console.log('Websocket chat: ' + e.type);
                }
                chatUi.disableForm();
            },
            onclose: function (e) {
                if (debug) {
                    console.log('Websocket chat: ' + e.type);
                }
                chatUi.disableForm();
            }
        });
    });

})(jQuery, Drupal);
