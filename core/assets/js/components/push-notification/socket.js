import * as utility from "Base/utility";

function pushnxSocket() {
    "use strict";

    var isconnected = false;
    var pushnx_debug = {};
    var push_notif_eb = {};

    this.connect = function (option) {
        var self = this;
        var opt = option || {};

        var pushnx_global = opt.global || {};
        var pushnx_message = opt.message || {};
        push_notif_eb = opt.eb || {};
        pushnx_debug = opt.console || {};

        var push_notif_player_address = opt.playerServer || {};
        var push_notif_server_address = opt.notifServer || {};

        pushnx_debug.notify("Eventbus: " + push_notif_eb);
        pushnx_debug.notify('Supports WebSocket: ' + self.hasWebsocket());

        push_notif_eb.onopen = function () {
            var json = {"playerId": pushnx_global.settings.playerId, "productId": pushnx_global.settings.productId};
            var headers = {"content-type":"application/json"};

            pushnx_debug.notify("Socket Connected: trying to register the player.");
            pushnx_debug.notify("Server: " + push_notif_server_address);
            pushnx_debug.notify("JSON: " + json);
            pushnx_debug.notify("Headers: " + headers);

            push_notif_eb.publish(push_notif_server_address, json, headers);

            pushnx_debug.console(json, 'Publish to Server', 'Published to ' + push_notif_server_address);
            pushnx_debug.notify('Player: ' + json.playerId);

            pushnx_debug.console(push_notif_player_address + pushnx_global.settings.playerId, 'Register to channel');
            pushnx_debug.notify('trying to register on channel ' + push_notif_player_address + pushnx_global.settings.playerId);
            push_notif_eb.registerHandler(
                push_notif_player_address + pushnx_global.settings.playerId,
                function (err, msg) {
                    if (err) {
                        pushnx_debug.console(err, 'Registration error');
                        pushnx_debug.notify('Error: ' + err);
                    }

                    pushnx_debug.console(msg, 'Message Received from Server');
                    pushnx_debug.notify('Message(s) received from Push Service!');
                    pushnx_debug.notify(msg.body.length);

                    if (msg.body !== null) {
                        isconnected = true;
                        self.isConnected();

                        pushnx_debug.console(msg.body, 'Raw Messages');
                        var extractedMsg = pushnx_message.extractMessage(msg.body);

                        var productMsg = pushnx_message.productMessage(extractedMsg);
                        pushnx_debug.console(productMsg, 'Filtered Messages by Product', 'Product Type Id: ' + pushnx_global.settings.productTypeId);

                        utility.triggerEvent(document, 'pnxMessagesByProduct', {
                            count: productMsg.length
                        });

                        pushnx_message.sendMessages(productMsg);
                    }
                }
            );
        };

        // socket close
        this.bindSocketClose();
    };

    this.isConnected = function () {
        utility.triggerEvent(document, 'pushnx.connected', {
            status: isconnected
        });
    };

    this.hasWebsocket = function () {
        return ("WebSocket" in window);
    };

    this.closeSocket = function (e) {
        if (e.customData.close && push_notif_eb) {
            push_notif_eb.close();
            pushnx_debug.notify('Close WebSocket!');
        }
    };

    this.bindSocketClose = function () {
        utility.addEventListener(document, 'pushnx.close', this.closeSocket);
    };

    this.unbindSocketClose = function () {
        utility.removeEventListener(document, 'pushnx.close', this.closeSocket);
    };
}

export default pushnxSocket;
