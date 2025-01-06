/**
 * Local Storage Adapter
 *
 * A custom storage adapter for shared data
 */
export default function Storage() {
    "use strict";

    var storage = {};

    this.get = function (index) {
        if (isSupported()) {
            return localStorage.getItem(index);
        }

        if (storage[index]) {
            return storage[index];
        }

        return null;
    };

    this.set = function (index, value) {
        if (isSupported()) {
            return localStorage.setItem(index, value);
        } else {
            storage[index] = value;
            return true;
        }
    };

    this.remove = function (index) {
        if (isSupported()) {
            return localStorage.removeItem(index);
        }

        if (storage[index]) {
            delete storage[index];
            return true;
        }

        return false;
    };

    /**
     * Check if localStorage is supported
     * @return {Boolean}
     */
    function isSupported() {
        if (localStorage) {
            return true;
        }

        return false;
    }
}
