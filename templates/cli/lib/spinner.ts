import progress from "cli-progress";
import chalk from "chalk";
import stringWidth from "string-width";

const SPINNER_ARC = "arc";
const SPINNER_DOTS = "dots";

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
    frames: ["◜", "◠", "◝", "◞", "◡", "◟"],
  },
  [SPINNER_DOTS]: {
    interval: 80,
    frames: ["⠋", "⠙", "⠹", "⠸", "⠼", "⠴", "⠦", "⠧", "⠇", "⠏"],
  },
};

class Spinner {
  static updatesBar: progress.MultiBar;
  private bar: progress.SingleBar;
  private spinnerInterval?: NodeJS.Timeout;
  private static readonly DEFAULT_MIDDLE_WIDTH = 40;
  private static readonly MIN_MIDDLE_WIDTH = 1;

  static start(
    clearOnComplete: boolean = true,
    hideCursor: boolean = true,
  ): void {
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

  static log(message: string): void {
    if (!message.endsWith("\n")) {
      message += "\n";
    }

    if (Spinner.updatesBar) {
      Spinner.updatesBar.log(message);
      return;
    }

    process.stdout.write(message);
  }

  static formatter(
    _options: unknown,
    _params: unknown,
    payload: SpinnerPayload,
  ): string {
    const status = payload.status.padEnd(12);
    const middle = `${payload.resource} (${payload.id})`;

    let prefix = chalk.cyan(payload.prefix ?? "⧗");
    let start = chalk.cyan(status);
    let end = chalk.yellow(payload.end ?? "");

    if (status.toLowerCase().trim() === "pushed") {
      start = chalk.greenBright.bold(status);
      prefix = chalk.greenBright.bold("✓");
      end = "";
    } else if (status.toLowerCase().trim() === "deploying") {
      start = chalk.cyanBright.bold(status);
    } else if (status.toLowerCase().trim() === "deployed") {
      start = chalk.green.bold(status);
      prefix = chalk.green.bold("✓");
    } else if (status.toLowerCase().trim() === "error") {
      start = chalk.red.bold(status);
      prefix = chalk.red.bold("✗");
      end = chalk.red(payload.errorMessage ?? "");
    }

    return Spinner.line(prefix, start, middle, end);
  }

  private static fitMiddle(
    prefix: string,
    start: string,
    middle: string,
    end: string,
    separator: string,
  ): string {
    const terminalWidth = process.stdout.columns ?? 120;
    const startWidth = stringWidth(`${prefix} ${start} ${separator} `);
    const endWidth = end ? stringWidth(` ${separator} ${end}`) : 0;
    const availableWidth = Math.max(
      Spinner.MIN_MIDDLE_WIDTH,
      terminalWidth - startWidth - endWidth,
    );
    const targetWidth = Math.min(Spinner.DEFAULT_MIDDLE_WIDTH, availableWidth);

    if (stringWidth(middle) <= targetWidth) {
      return middle.padEnd(targetWidth);
    }

    if (targetWidth <= 1) {
      return "…";
    }

    let truncated = "";
    for (const char of middle) {
      const next = `${truncated}${char}`;
      if (stringWidth(`${next}…`) > targetWidth) {
        break;
      }
      truncated = next;
    }

    return `${truncated}…`;
  }

  static line(
    prefix: string,
    start: string,
    middle: string,
    end: string,
    separator: string = "•",
  ): string {
    const fittedMiddle = Spinner.fitMiddle(
      prefix,
      start,
      middle,
      end,
      separator,
    );

    return `${prefix} ${start} ${separator} ${fittedMiddle} ${!end ? "" : separator} ${end}`;
  }

  constructor(
    payload: SpinnerPayload,
    total: number = 100,
    startValue: number = 0,
  ) {
    this.bar = Spinner.updatesBar.create(total, startValue, payload);
  }

  update(payload: Partial<SpinnerPayload>): this {
    this.bar.update(payload);
    return this;
  }

  fail(payload: Partial<SpinnerPayload>): void {
    this.stopSpinner();
    this.update({ status: "Error", ...payload });
  }

  startSpinner(name: string): void {
    let spinnerFrame = 1;
    const spinner = spinners[name] ?? spinners["dots"];

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
