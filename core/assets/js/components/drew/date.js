/**
 * Date handlers component
 */
export default (function () {
    var DateUtil = {};

    /**
     * Returns a date object for 00:00:00 local time
     *
     * @param string date
     * @param string format compose of day month and year
     */
    DateUtil.parseDate = function (date, format) {
        format = format || "DD/MM/YYYY";

        if (date === "") {
            return null;
        }

        var dateString = date.match(/(\d+)/g),
            dateArrayFormat = format.match(/([a-zA-Z]+)/g),
            dateFormat = [];

        for (var i = 0; i <= dateArrayFormat.length - 1; i++) {
            if (dateArrayFormat[i] === 'YYYY') {
                dateFormat[0] = i;
            }

            if (dateArrayFormat[i] === 'MM') {
                dateFormat[1] = i;
            }

            if (dateArrayFormat[i] === 'DD') {
                dateFormat[2] = i;
            }
        }

        return new Date(dateString[dateFormat[0]],
            dateString[dateFormat[1]] - 1,
            dateString[dateFormat[2]]
        );
    };

    return DateUtil;
})();
