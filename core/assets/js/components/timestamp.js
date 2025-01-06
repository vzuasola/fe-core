import * as utility from "Base/utility";

/**
 * Timestamp for the Header
 * Timestamp for the localisation of the User Time.
 */
utility.ready(function () {
    var el = document.getElementById("time_ticker"),
        currentTime = new Date(Date.parse(app.settings.timestamp.time)),
        gmtTime = app.settings.timestamp.offset >= 0
            ? '+' + app.settings.timestamp.offset
            : app.settings.timestamp.offset;

    /**
     * Function to get the localised time and date.
     */
    function timeStamp() {
        // Since this is inside an interval, we set variables to null to force trigger browser GC (IE8 below)
        // currentTime = null;
        var now = currentTime.getTime(), // convert current time in ms
            dateObj = null,
            time = null,
            timestamp = null,
            langKey = null,
            date = null;

        // add 1s to current time
        now += 1000;

        // set the updated time
        currentTime.setTime(now);

        dateObj = {
            "month":("0" + (currentTime.getMonth() + 1)).slice(-2),
            "day":("0" + currentTime.getDate()).slice(-2),
            "year":currentTime.getFullYear(),
            "hours":("0" + currentTime.getHours()).slice(-2),
            "minutes":("0" + currentTime.getMinutes()).slice(-2),
            "seconds": ("0" + currentTime.getSeconds()).slice(-2)
        };

        // time = dateObj.hours + ":" + dateObj.minutes + ":" + dateObj.seconds,
        time = dateObj.hours + ":" + dateObj.minutes;
        langKey = app.settings.lang;

        // Process the format based on language
        switch (langKey.toLowerCase()) {
            case "en":
            case "vn":
            case "in":
            case "th":
            case "id":
            case "eu":
            case "gr":
            case "pl":
                date = [dateObj.day, dateObj.month, dateObj.year].join("/");
                break;
            case "sc":
            case "ch":
            case "kr":
            case "jr":
                date = [dateObj.year, dateObj.month, dateObj.day].join("/");
                break;
            default:
                date = [dateObj.year, dateObj.month, dateObj.day].join("/");
                break;
        }

        timestamp = date + " " + time + " " + "(GMT" + gmtTime + ")";
        el.innerHTML = timestamp;
    }

    // function to call ticker() function after an interval.
    setInterval(timeStamp, 1000);
});
