/**
 * Entity component
 */
export default (function () {
    var Entity = {};

    /**
     * Filter entities by login state
     *
     * @param string collection
     *
     * @return array
     */
    Entity.filterCollectionByLoginState = function (collection) {
        var temp = [];

        temp = collection.filter(function (entity) {
            return Entity.filterByLoginState(entity['states']);
        });

        return temp;
    };

    /**
     * Checks login state availability of entities
     *
     * @param array states
     *
     * @return boolean
     */
    Entity.filterByLoginState = function (states) {
        var state,
            isLogin = app.settings.login || false;

        for (var i = 0; i < states.length; i++ ) {
            state = parseInt(states[i]['value']);

            switch (true) {
                case state === 2:
                case state === 0 && !isLogin:
                case state === 1 && isLogin:
                    return true;
            }
        }

        return false;
    };

    return Entity;
})();
