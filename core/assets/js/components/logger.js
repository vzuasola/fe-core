import xhr from "BaseVendor/reqwest";
import Console from "Base/debug/console";

var Logger = {};
/**
 * Static log function
 *
 * @param object data
 */
Logger.log = function (workflow, data) {
    if (parseInt(app.settings.logger_disable) === 1) {
        return;
    }

    var result = app.settings.metrics_log.data;

    if (typeof data === 'object') {
        var workflows = app.settings.metrics_log.workflows;

        data.workflow = workflow.toLowerCase();

        for (var i = 0; i < workflows.length; i++) {
            var index = workflows[i];

            if (data.hasOwnProperty(index)) {
                result[index] = data[index];
            } else if (typeof result[index] === 'undefined') {
                result[index] = "";
            }
        }
    }

    if (typeof result['options'] !== 'string') {
        result['options'] = JSON.stringify(result['options']);
    }

    if (typeof result['request'] !== 'string') {
        result['request'] = JSON.stringify(result['request']);
    }

    if (typeof result['response'] !== 'string') {
        result['response'] = JSON.stringify(result['response']);
    }

    Console.log('Metrics Log entry for ' + workflow, result);

    xhr({
        url: app.settings.logger_url,
        type: 'json',
        method: 'post',
        data: result
    });
};

export default Logger;
