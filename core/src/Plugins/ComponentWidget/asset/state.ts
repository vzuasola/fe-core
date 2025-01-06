/**
 *
 */
export class State {
    constructor(private list: any[], private success?) {
    }

    markAsDone(id) {
        const index = this.list.indexOf(id);

        if (index > -1) {
            this.list.splice(index, 1);
        }

        if (this.list.length === 0) {
            if (this.success) {
                this.success();
            }
        }
    }
}
