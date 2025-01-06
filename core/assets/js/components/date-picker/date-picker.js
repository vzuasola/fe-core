var Pikaday = require("BaseVendor/pikaday");

var dobField = document.getElementById('RegistrationForm_birthdate'),
    dateFormat = dobField.getAttribute('date-format') || "DD/MM/YYYY";

new Pikaday({
    field: dobField,
    format: dateFormat,
    toString: function (date, format) {
        // you should do formatting based on the passed format,
        // but we will just return 'D/M/YYYY' for simplicity
        var day = date.getDate();
        var month = date.getMonth() + 1;
        var year = date.getFullYear();
        return day + "/" + month + "/" + year;
    },
    parse: function (dateString, format) {
        // dateString is the result of `toString` method
        var parts = dateString.split('/');
        var day = parseInt(parts[0], 10);
        var month = parseInt(parts[1] - 1, 10);
        var year = parseInt(parts[1], 10);
        return new Date(year, month, day);
    }
});
