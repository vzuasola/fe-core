/**
 * Session Storage Adapter
 *
 * A custom storage adapter for shared data
 */
export default function SessionStorage() {
    "use strict";

    var storage = {};

    this.get = function (index) {
        if (isSupported()) {
            return sessionStorage.getItem(index);
        }

        if (storage[index]) {
            return storage[index];
        }

        return null;
    };

    this.set = function (index, value) {
        if (isSupported()) {
            return sessionStorage.setItem(index, value);
        } else {
            storage[index] = value;
            return true;
        }
    };

    this.remove = function (index) {
        if (isSupported()) {
            return sessionStorage.removeItem(index);
        }

        if (storage[index]) {
            delete storage[index];
            return true;
        }

        return false;
    };

    /**
     * Check if sessionStorage is supported
     * @return {Boolean}
     */
    function isSupported() {
        if (sessionStorage) {
            return true;
        }

        return false;
    }
}
