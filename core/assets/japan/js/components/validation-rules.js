/**
 * Common FormValidator rules shared between sites
 */

/**
 * must_contain_alpha_numeric
 *
 * Forces value to contain at least one of each:
 *    * One lowercase latin character
 *    * One uppercase latin character
 *    * One number
 *    * Only alphanum lower or capital case
 */
export function must_contain_alpha_numeric(value, param, field) {
    /**
     * Use a positve lookahead for each rule,
     * check for the rest of the allowed characters followed by
     * the group currently being checked
     *
     * eg: (?=[a-z0-9]*?[A-Z]) Look for any lowercase or digit followed by uppercase and return to start
     */

    var reSource = [
        /^/,
        /(?=[a-z0-9]*?[A-Z])/,
        /(?=[A-Z0-9]*?[a-z])/,
        /(?=[a-zA-Z]*?[0-9])/,
        /[a-zA-Z0-9]*/, // After the lookaheads verify only the allowed characters exist in the string
        /$/
    ];

    var re = new RegExp(reSource.map(function (r) {
        return r.source;
    }).join(''));

    return re.test(String(value));
}
