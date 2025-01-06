import * as utility from "Base/utility";

export default function gameIframe() {
    utility.ready(function () {
        utility.addEventListener(document.body, 'click', function (event) {
            event = event || window.event;
            var target = event.target || event.srcElement;
            if (utility.hasClass(target, 'btn-logout')) {
                window.location.href = target.href;
                window.close();
                window.onunload = refreshParent;
            }
            function refreshParent() {
                window.opener.location.reload();
            }
        });
    });

}

function adjustiframe(contentHeight) {
    var iFrameID = document.getElementById('contentiframe');
    if (iFrameID) {
        iFrameID.height = contentHeight;
        var iFrameDiv = document.getElementById('iframe-container');

        if (iFrameDiv) {
            iFrameDiv.style.height = contentHeight;
        }
    }
}

function receiveMessage(event) {
    if (event.origin !== app.settings.virtual_sports) {
        return;
    }

    if (event.data.command !== undefined && event.data.command === "adjustiframe") {
        adjustiframe(event.data.height);
    }
}

window.addEventListener("message", receiveMessage, false);
gameIframe();
