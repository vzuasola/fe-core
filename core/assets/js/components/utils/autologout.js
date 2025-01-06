import xhr from "BaseVendor/reqwest";
import * as utility from "Base/utility";

/**
 * Logs out the player automatically after 1 hour of inactivity.
 *
 * A wake-up call is a call to an endpoint that reaches iCore. It is used to update the player's session
 * on iCore.
 *
 * @param int maxInactivityPeriod The maximum period in seconds a user can be inactive before we log him out.
 * @param array options Available options
 *
 * Available options
 *        int refresh How often (in msec) to check for session expiration due to inactivity.
 *        int minWakeUpPeriod The minimum period between 2 subsequent wake-up calls
 *
 *        closure onStop(this) A closure to invoke when the counter has stop
 */
export default function AutoLogout(maxInactivityPeriod, options) {
    "use strict";

    var $this = this,
        $timeoutDate,
        $lastWakeUpCallDate,
        $intervalId;

    function construct() {
        var defaults = {
            refresh: 1000,
            minWakeUpPeriod: 10 * 60 * 1000,
            onRestart: false,
            onStop: false,
        };

        // extend options
        options = options || {};

        for (var name in defaults) {
            if (options[name] === undefined) {
                options[name] = defaults[name];
            }
        }

        $timeoutDate = null;
        $lastWakeUpCallDate = null;
    }

    construct();

    this.start = function () {
        var currentDate, newTimeoutDate;
        currentDate = new Date();
        newTimeoutDate = new Date();
        newTimeoutDate.setSeconds( newTimeoutDate.getSeconds() + maxInactivityPeriod);

        $lastWakeUpCallDate = currentDate;
        $timeoutDate = newTimeoutDate;

        $intervalId = setInterval(checkIfLogoutIsNeeded, options.refresh);
    };

    this.restart = function () {
        if (typeof options.onRestart === 'function') {
            options.onRestart($this, $timeoutDate);
        }

        $this.kill();
        $this.start();
    };

    this.onUserActivity = function () {
        if ($intervalId == null) { // We need this because user action events still trigger reset() after logout
            return;
        }

        var currentDate = new Date();
        var newTimeoutDate = new Date();

        newTimeoutDate.setSeconds( newTimeoutDate.getSeconds() + maxInactivityPeriod);
        $timeoutDate = newTimeoutDate;

        var timeSinceLastWakeUp = currentDate.getTime() - $lastWakeUpCallDate.getTime();
        if (timeSinceLastWakeUp >= options.minWakeUpPeriod) {
            // If we don't set this date while making the call, it may trigger again
            // due to another user action before we get the response.
            $lastWakeUpCallDate = currentDate;
            // A call that involves iCore. iCore needs to know the user session is active in case no similar
            // call (involving iCore) was made all this time.
            var wakeUpUrl = utility.url('/is-logged-in');

            xhr({
                url: wakeUpUrl,
                method: 'post',
            }).then(function (response) {
                if (!response.active) {
                    $this.stop();
                }
            }).fail(function (err, msg) {
                console.log("Wake up call failed.");
            });
        }
    };

    this.kill = function () {
        if (!($intervalId == null)) {
            $intervalId = clearInterval($intervalId);
        }

        $timeoutDate = null;
        $lastWakeUpCallDate = null;
    };

    this.stop = function () {
        $intervalId = clearInterval($intervalId);

        if (typeof options.onStop === 'function') {
            options.onStop($this);
        }
    };

    function checkIfLogoutIsNeeded() {
        var currentDate = new Date();

        if (currentDate.getTime() >= $timeoutDate.getTime()) {
            $this.stop();
        }
    }
}
