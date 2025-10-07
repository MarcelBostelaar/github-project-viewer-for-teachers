
// Returns the running average elapsed time per request in ms
function getAverageElapsedTime() {
    return elapsedCount === 0 ? 0 : runningAverage / elapsedCount;
}

class APIpooler{
    MAX_CONCURRENT = 5;
    BURST_SIZE = 20; //number of requests to start at once
    BURST_AT_SPEED = 250;
    running = 0;
    queue = [];

    // Track total elapsed time and count for running average
    runningAverage = 10000;

    promiseDrain;
    promiseResolve;

    registerCallbackInQueue(callback) {
        this.queue.push(callback);
    }

    launchOneOffQueue(){
        const callback = this.queue.shift();
        if(!callback) return;
        this.running++;
        const start = Date.now();
        callback().finally(() => {
            let elapsed = Date.now() - start;
            this.runningAverage = (this.runningAverage + elapsed) / 2; // Simple moving average
            // console.log("Elapsed time: " + elapsed.toString() + "\nAverage time: " + runningAverage.toString() + " ms");
            this.running--;
            this._processQueue();
        });
    }

    processQueue() {
        if (!this.promiseDrain) {
            this.promiseDrain = new Promise((resolve) => {
                this.promiseRevolve = resolve;
            });
            this._processQueue();
        }
        return this.promiseDrain;
    }

    _processQueue() {
        if(this.runningAverage < this.BURST_AT_SPEED){
            //If we are fast, launch a burst of requests
            let burstCount = Math.min(this.BURST_SIZE, this.queue.length);
            // console.log("Bursting " + burstCount + " requests");
            this.runningAverage = 10000; //reset average to avoid repeated bursts
            for(let i=0; i<burstCount; i++){
                this.launchOneOffQueue();
            }   
            return;
        }
        while (this.running < this.MAX_CONCURRENT && this.queue.length > 0) {
            this.launchOneOffQueue();
        }
        if(this.running === 0 && this.queue.length === 0 && this.promiseRevolve){
            this.promiseRevolve();
            this.promiseDrain = null;
            this.promiseRevolve = null;
        }
    }
}