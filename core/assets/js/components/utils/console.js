import * as utility from "Base/utility";

function Console() {
    this.items = [];

    this.push = function (key, value, group) {
        utility.invoke(document, 'console.push', {
            key: key,
            value: value,
            group: group,
        });

        this.items.push({
            key: key,
            value: value,
            group: group,
        });
    };

    this.getItems = function () {
        return this.items;
    };
}

var console = new Console();

window.utilConsole = console;

export {console as Console};
