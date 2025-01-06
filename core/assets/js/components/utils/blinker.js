/**
 * Blinks the title bar
 *
 * @param string title
 * @param integer rate
 */
export default function Blinker(title, rate) {
    "use strict";

    var $this = this,
        $blinker,
        $title = document.title,
        $elapsed = 0;

    rate = rate || 500;

    /**
     *
     */
    this.start = function (seconds) {
        $blinker = setInterval(function () {
            if (typeof seconds !== 'undefined' &&
                $elapsed >= seconds * 1000
            ) {
                $this.stop();
            } else {
                $elapsed += rate;
                document.title = (document.title === $title) ? title : $title;
            }
        }, rate);
    };

    /**
     *
     */
    this.stop = function () {
        clearInterval($blinker);

        $elapsed = 0;
        document.title = $title;
    };
}
