import * as utility from "Base/utility";

utility.ready( function () {
    if (document.body.classList.contains('agent-player')) {
        var profileLabel = document.querySelector('.my-profile-link');

        utility.removeAttributes(profileLabel, ['href', 'data-popup']);
    }
});
