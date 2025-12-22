import progress = require('cli-progress');
import chalk = require('chalk');

const SPINNER_ARC = 'arc';
const SPINNER_DOTS = 'dots';

interface SpinnerConfig {
    interval: number;
    frames: string[];
}

interface SpinnerPayload {
    status: string;
    resource: string;
    id: string;
    prefix?: string;
    end?: string;
    errorMessage?: string;
}

const spinners: Record<string, SpinnerConfig> = {
    [SPINNER_ARC]: {
        interval: 100,
        frames: ['◜', '◠', '◝', '◞', '◡', '◟'],
    },
    [SPINNER_DOTS]: {
        interval: 80,
        frames: ['⠋', '⠙', '⠹', '⠸', '⠼', '⠴', '⠦', '⠧', '⠇', '⠏'],
    },
};

class Spinner {
    static updatesBar: progress.MultiBar;
    private bar: progress.SingleBar;
    private spinnerInterval?: NodeJS.Timeout;

    static start(clearOnComplete: boolean = true, hideCursor: boolean = true): void {
        Spinner.updatesBar = new progress.MultiBar({
            format: this.formatter,
            hideCursor,
            clearOnComplete,
            stopOnComplete: true,
            linewrap: true,
            noTTYOutput: true,
        });
    }

    static stop(): void {
        Spinner.updatesBar.stop();
    }

    static formatter(options: any, params: any, payload: SpinnerPayload): string {
        const status = payload.status.padEnd(12);
        const middle = `${payload.resource} (${payload.id})`.padEnd(40);

        let prefix = chalk.cyan(payload.prefix ?? '⧗');
        let start = chalk.cyan(status);
        let end = chalk.yellow(payload.end ?? '');

        if (status.toLowerCase().trim() === 'pushed') {
            start = chalk.greenBright.bold(status);
            prefix = chalk.greenBright.bold('✓');
            end = '';
        } else if (status.toLowerCase().trim() === 'deploying') {
            start = chalk.cyanBright.bold(status);
        } else if (status.toLowerCase().trim() === 'deployed') {
            start = chalk.green.bold(status);
            prefix = chalk.green.bold('✓');
        } else if (status.toLowerCase().trim() === 'error') {
            start = chalk.red.bold(status);
            prefix = chalk.red.bold('✗');
            end = chalk.red(payload.errorMessage ?? '');
        }

        return Spinner.line(prefix, start, middle, end);
    }

    static line(prefix: string, start: string, middle: string, end: string, separator: string = '•'): string {
        return `${prefix} ${start} ${separator} ${middle} ${!end ? '' : separator} ${end}`;
    }

    constructor(payload: SpinnerPayload, total: number = 100, startValue: number = 0) {
        this.bar = Spinner.updatesBar.create(total, startValue, payload);
    }

    update(payload: Partial<SpinnerPayload>): this {
        this.bar.update(payload);
        return this;
    }

    fail(payload: Partial<SpinnerPayload>): void {
        this.stopSpinner();
        this.update({ status: 'Error', ...payload });
    }

    startSpinner(name: string): void {
        let spinnerFrame = 1;
        const spinner = spinners[name] ?? spinners['dots'];

        this.spinnerInterval = setInterval(() => {
            if (spinnerFrame === spinner.frames.length) spinnerFrame = 1;
            this.bar.update({ prefix: spinner.frames[spinnerFrame++] });
        }, spinner.interval);
    }

    stopSpinner(): void {
        if (this.spinnerInterval) {
            clearInterval(this.spinnerInterval);
        }
    }

    replaceSpinner(name: string): void {
        this.stopSpinner();
        this.startSpinner(name);
    }
}

export { Spinner, SPINNER_ARC, SPINNER_DOTS };
