import * as utility from "Base/utility";

import "Base/login/login-manager";
import "Base/balance/balance-manager";
import "Base/dafacoin-menu/dafacoin-menu-manager";
import "Base/dafacoin-menu/dafacoin-popup";
import "Base/livechat/avaya/avaya-view";
import "Base/partner-matrix/header";

import passwordMask from "Base/password-mask";
import capsLockNotification from "Base/capslock-notification";
import LoginFormLightBox from "Base/login/login-form";
import languageSwitcher from "Base/language-switcher";

languageSwitcher();

utility.ready(function () {
    new LoginFormLightBox();

    var password = document.getElementById("LoginForm_password");

    passwordMask(password);
    capsLockNotification(password);
});
