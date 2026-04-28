import fs from "fs";
import os from "os";
import path from "path";
import { Command } from "commander";
import type { Option } from "commander";
import { EXECUTABLE_NAME } from "./constants.js";

const SUPPORTED_COMPLETION_SHELLS = ["zsh", "bash", "fish"] as const;

type CompletionShell = (typeof SUPPORTED_COMPLETION_SHELLS)[number];

type CompletionOption = {
  flags: string[];
  required: boolean;
  optional: boolean;
};

type CompletionCommand = {
  name: string;
  aliases: string[];
  options: CompletionOption[];
  commands: CompletionCommand[];
  path: string[];
};

type CompletionContext = {
  path: string[];
  commands: string[];
  options: string[];
};

type CompletionTransition = {
  from: string;
  token: string;
  to: string;
};

function uniq(values: string[]): string[] {
  return [...new Set(values)];
}

function shellQuote(value: string): string {
  return `'${value.replace(/'/g, `'\\''`)}'`;
}

function commandPath(path: string[]): string {
  return path.join(" ");
}

function optionToCompletion(option: Option): CompletionOption {
  return {
    flags: uniq(
      [option.short, option.long].filter((flag): flag is string =>
        Boolean(flag),
      ),
    ),
    required: option.required,
    optional: option.optional,
  };
}

function commandToCompletion(
  command: Command,
  path: string[] = [],
): CompletionCommand {
  const helper = command.createHelp();
  const commands = helper
    .visibleCommands(command)
    .filter((childCommand) => childCommand.name() !== "help")
    .map((childCommand) =>
      commandToCompletion(childCommand, [...path, childCommand.name()]),
    );

  return {
    name: command.name(),
    aliases: command.aliases(),
    options: helper.visibleOptions(command).map(optionToCompletion),
    commands,
    path,
  };
}

function commandCompletionNames(command: CompletionCommand): string[] {
  return uniq([command.name, ...command.aliases]);
}

function collectCompletionContexts(
  command: CompletionCommand,
  rootOptions: CompletionOption[],
  contexts: CompletionContext[] = [],
): CompletionContext[] {
  const options =
    command.path.length === 0
      ? command.options
      : [...rootOptions, ...command.options];

  contexts.push({
    path: command.path,
    commands: command.commands.flatMap(commandCompletionNames),
    options: uniq(options.flatMap((option) => option.flags)),
  });

  for (const childCommand of command.commands) {
    collectCompletionContexts(childCommand, rootOptions, contexts);
  }

  return contexts;
}

function collectCompletionTransitions(
  command: CompletionCommand,
  transitions: CompletionTransition[] = [],
): CompletionTransition[] {
  const from = commandPath(command.path);

  for (const childCommand of command.commands) {
    const to = commandPath(childCommand.path);

    for (const name of commandCompletionNames(childCommand)) {
      transitions.push({ from, token: name, to });
    }

    collectCompletionTransitions(childCommand, transitions);
  }

  return transitions;
}

function completionSpec(command: Command): {
  contexts: CompletionContext[];
  transitions: CompletionTransition[];
  root: CompletionCommand;
} {
  const root = commandToCompletion(command);

  return {
    contexts: collectCompletionContexts(root, root.options),
    transitions: collectCompletionTransitions(root),
    root,
  };
}

function renderZshCompletion(command: Command): string {
  const spec = completionSpec(command);
  const commandName = EXECUTABLE_NAME;
  const transitionCases = spec.transitions
    .map(
      (transition) =>
        `        ${shellQuote(`${transition.from}:${transition.token}`)}) context=${shellQuote(transition.to)} ;;`,
    )
    .join("\n");
  const contextCases = spec.contexts
    .map((context) => {
      const completions = uniq([...context.commands, ...context.options])
        .map(shellQuote)
        .join(" ");

      return `        ${shellQuote(commandPath(context.path))})
            completions=( ${completions} )
            ;;`;
    })
    .join("\n");

  return `#compdef ${commandName}

_${commandName}() {
    local context word
    local -a completions

    context=''
    for (( i = 2; i < CURRENT; i++ )); do
        word="\${words[i]}"
        [[ "\${word}" == -* ]] && continue

        case "\${context}:\${word}" in
${transitionCases}
        esac
    done

    case "\${context}" in
${contextCases}
    esac

    compadd -- "\${completions[@]}"
}

if (( $+functions[compdef] )); then
    compdef _${commandName} ${commandName}
fi
`;
}

function renderBashCompletion(command: Command): string {
  const spec = completionSpec(command);
  const commandName = EXECUTABLE_NAME;
  const transitionCases = spec.transitions
    .map(
      (transition) =>
        `            ${shellQuote(`${transition.from}:${transition.token}`)}) context=${shellQuote(transition.to)} ;;`,
    )
    .join("\n");
  const contextCases = spec.contexts
    .map((context) => {
      const completions = uniq([...context.commands, ...context.options]).join(
        " ",
      );

      return `        ${shellQuote(commandPath(context.path))})
            completions=${shellQuote(completions)}
            ;;`;
    })
    .join("\n");

  return `_${commandName}_completion() {
    local cur context completions word i

    cur="\${COMP_WORDS[COMP_CWORD]}"
    context=''
    completions=''

    for (( i = 1; i < COMP_CWORD; i++ )); do
        word="\${COMP_WORDS[i]}"
        [[ "\${word}" == -* ]] && continue

        case "\${context}:\${word}" in
${transitionCases}
        esac
    done

    case "\${context}" in
${contextCases}
    esac

    COMPREPLY=( $(compgen -W "\${completions}" -- "\${cur}") )
}

if type complete >/dev/null 2>&1; then
    complete -F _${commandName}_completion ${commandName}
fi
`;
}

function fishCondition(commandName: string, path: string[]): string {
  const pathText = commandPath(path);

  return pathText
    ? `__${commandName}_using_command ${pathText}`
    : `__${commandName}_using_command`;
}

function fishOptionLine(
  commandName: string,
  path: string[],
  option: CompletionOption,
): string | null {
  const shortFlag = option.flags.find((flag) => /^-[^-]$/.test(flag));
  const longFlag = option.flags.find((flag) => /^--/.test(flag));

  if (!shortFlag && !longFlag) {
    return null;
  }

  const parts = [
    "complete",
    "-c",
    shellQuote(commandName),
    "-f",
    "-n",
    shellQuote(fishCondition(commandName, path)),
  ];

  if (shortFlag) {
    parts.push("-s", shellQuote(shortFlag.slice(1)));
  }

  if (longFlag) {
    parts.push("-l", shellQuote(longFlag.slice(2)));
  }

  if (option.required) {
    parts.push("-r");
  } else if (option.optional) {
    parts.push("-x");
  }

  return parts.join(" ");
}

function renderFishCompletion(command: Command): string {
  const commandName = EXECUTABLE_NAME;
  const spec = completionSpec(command);
  const transitionCases = spec.transitions
    .map(
      (transition) =>
        `            case ${shellQuote(`${transition.from}:${transition.token}`)}
                set context ${shellQuote(transition.to)}`,
    )
    .join("\n");
  const contextLines = spec.contexts
    .flatMap((context) => {
      const lines: string[] = [];
      const commandNames = uniq(context.commands);

      if (commandNames.length > 0) {
        lines.push(
          `complete -c ${shellQuote(commandName)} -f -n ${shellQuote(fishCondition(commandName, context.path))} -a ${shellQuote(commandNames.join(" "))}`,
        );
      }

      const commandNode =
        context.path.length === 0
          ? spec.root
          : findCompletionCommand(spec.root, context.path);
      const rootOptions = spec.root.options;
      const optionSource =
        context.path.length === 0 || !commandNode
          ? rootOptions
          : [...rootOptions, ...commandNode.options];

      for (const option of dedupeCompletionOptions(optionSource)) {
        const line = fishOptionLine(commandName, context.path, option);

        if (line) {
          lines.push(line);
        }
      }

      return lines;
    })
    .join("\n");

  return `function __${commandName}_using_command
    set -l tokens (commandline -opc)
    set -l context ''
    set -l expected (string join ' ' $argv)

    if test (count $tokens) -gt 0
        set -e tokens[1]
    end

    for token in $tokens
        if string match -qr '^-' -- $token
            continue
        end

        switch "$context:$token"
${transitionCases}
        end
    end

    test "$context" = "$expected"
end

${contextLines}
`;
}

function findCompletionCommand(
  command: CompletionCommand,
  path: string[],
): CompletionCommand | null {
  if (commandPath(command.path) === commandPath(path)) {
    return command;
  }

  for (const childCommand of command.commands) {
    const found = findCompletionCommand(childCommand, path);

    if (found) {
      return found;
    }
  }

  return null;
}

function dedupeCompletionOptions(
  options: CompletionOption[],
): CompletionOption[] {
  const seen = new Set<string>();
  const deduped: CompletionOption[] = [];

  for (const option of options) {
    const key = option.flags.join(",");

    if (seen.has(key)) {
      continue;
    }

    seen.add(key);
    deduped.push(option);
  }

  return deduped;
}

function renderCompletion(command: Command, shell: CompletionShell): string {
  if (shell === "zsh") {
    return renderZshCompletion(command);
  }

  if (shell === "bash") {
    return renderBashCompletion(command);
  }

  return renderFishCompletion(command);
}

function normalizeShell(value: string | undefined): CompletionShell | null {
  if (!value) {
    return null;
  }

  const shellName = path.basename(value).toLowerCase();

  if (shellName === "zsh" || shellName === "bash" || shellName === "fish") {
    return shellName;
  }

  return null;
}

function detectShell(): CompletionShell | null {
  return normalizeShell(process.env.SHELL);
}

function xdgPath(envName: string, fallback: string): string {
  const configuredPath = process.env[envName];

  if (configuredPath) {
    return configuredPath;
  }

  return fallback;
}

function completionInstallPath(
  commandName: string,
  shell: CompletionShell,
): string {
  const homeDir = os.homedir();

  if (shell === "zsh") {
    return path.join(
      xdgPath("ZSH_COMPLETION_DIR", path.join(homeDir, ".zfunc")),
      `_${commandName}`,
    );
  }

  if (shell === "bash") {
    return path.join(
      xdgPath(
        "BASH_COMPLETION_DIR",
        path.join(homeDir, ".local", "share", "bash-completion", "completions"),
      ),
      commandName,
    );
  }

  return path.join(
    xdgPath(
      "FISH_COMPLETION_DIR",
      path.join(homeDir, ".config", "fish", "completions"),
    ),
    `${commandName}.fish`,
  );
}

function installCompletion(
  rootCommand: Command,
  shell: CompletionShell,
): string {
  const installPath = completionInstallPath(EXECUTABLE_NAME, shell);

  fs.mkdirSync(path.dirname(installPath), { recursive: true });
  fs.writeFileSync(installPath, renderCompletion(rootCommand, shell));

  return installPath;
}

export function isCompletionInvocation(): boolean {
  return findTopLevelCommandArg(process.argv.slice(2)) === "completion";
}

function findTopLevelCommandArg(args: string[]): string | undefined {
  let consumesIdValues = false;

  for (const arg of args) {
    if (arg === "--") {
      return undefined;
    }

    if (consumesIdValues) {
      if (!arg.startsWith("-")) {
        continue;
      }

      consumesIdValues = false;
    }

    if (arg === "--id") {
      consumesIdValues = true;
      continue;
    }

    if (arg.startsWith("--id=")) {
      continue;
    }

    if (arg.startsWith("-")) {
      continue;
    }

    return arg;
  }

  return undefined;
}

export function isCompletionCommand(command: Command): boolean {
  let currentCommand: Command | undefined = command;

  while (currentCommand) {
    if (currentCommand.name() === "completion") {
      return true;
    }

    currentCommand = currentCommand.parent;
  }

  return false;
}

export function createCompletionCommand(rootCommand: Command): Command {
  const completionCommand = new Command("completion").description(
    "Generate shell completion scripts",
  );

  completionCommand
    .command("install [shell]")
    .description("Install shell completion script")
    .action((requestedShell?: string) => {
      const shell = normalizeShell(requestedShell) ?? detectShell();

      if (!shell) {
        process.stderr.write(
          `Unable to detect shell. Run ${EXECUTABLE_NAME} completion install zsh, bash, or fish.\n`,
        );
        process.exitCode = 1;
        return;
      }

      const installPath = installCompletion(rootCommand, shell);
      process.stdout.write(`Installed ${shell} completion to ${installPath}\n`);
    });

  for (const shell of SUPPORTED_COMPLETION_SHELLS) {
    completionCommand
      .command(shell)
      .description(`Generate ${shell} completion script`)
      .action(() => {
        process.stdout.write(renderCompletion(rootCommand, shell));
      });
  }

  return completionCommand;
}
