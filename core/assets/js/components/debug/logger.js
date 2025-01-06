// import * as utility from "Base/utility";
// import Console from "Base/debug/console";
// import xhr from "BaseVendor/reqwest";

export default (function () {
    var Logger = {};

    /**
     * Log to access_logs
     */
    Logger.log = function (workflow, details) {
        // try {
        //     var params = {
        //         x: '',
        //         s: '',
        //         c: '',
        //         r: '',
        //         u: '',
        //         p: {},
        //         t: new Date().getTime()
        //     };

        //     if (typeof workflow !== 'string') {
        //         throw new Error('Undefined required parameter: workflow');
        //     }

        //     // Check default params
        //     for (var k in params) {
        //         if ('x' === k) {
        //             params[k] = workflow;
        //             continue;
        //         }

        //         if ('p' === k) {
        //             if (typeof details[k] === 'object') {
        //                 params[k] = JSON.stringify(details[k]);
        //             } else {
        //                 params[k] = details[k];
        //             }
        //             continue;
        //         }

        //         if ('t' === k) {
        //             continue;
        //         }

        //         if (details[k] === undefined) {
        //             throw new Error('Undefined required parameter: ' + k);
        //         }

        //         params[k] = details[k].toString();
        //     }

        //     // Fetch the balance from server
        //     xhr({
        //         url: utility.url('/t'),
        //         type: 'json',
        //         data: params
        //     }).then(function (response) {
        //         Console.log('Client logging success');
        //     }).fail(function (err, msg) {
        //         Console.log('Client logging failed');
        //     });
        // } catch (e) {
        //     Console.log(e.message);
        // }
    };

    return Logger;
})();
