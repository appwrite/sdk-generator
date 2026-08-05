#!/usr/bin/env node
// Extracts the CLI's command surface from the generated TypeScript into a JSON
// contract the Go rewrite is checked against.
//
// The TypeScript CLI is the source of truth for flags, aliases, and hidden
// state, so the contract is captured from it rather than from a reviewer's
// memory. internal/cmd/surface_test.go diffs the Go command tree against the
// result, and the Checks / Go CLI job runs that test.
//
// RUN THIS when the API spec adds or removes commands: the contract is a
// checked-in snapshot, so a spec that has moved on makes the surface test fail
// with "command not in the contract". Regenerate from a fresh examples/cli:
//
//   php example.php cli
//   node scripts/go-cli/extract-command-surface.mjs
//
// A failure is only ever one of two things -- a stale contract, or the two CLIs
// genuinely drifting. Check which before regenerating.
//
// Usage: node scripts/go-cli/extract-command-surface.mjs [servicesDir] [outFile]

import fs from 'node:fs';
import path from 'node:path';

const servicesDir = process.argv[2] ?? 'examples/cli/lib/commands/services';
const outFile = process.argv[3] ?? 'templates/go-cli/internal/cmd/testdata/command-surface.json';

// `.command(`name`)` and `.command(`name`, { hidden: true })`
const SUBCOMMAND = /^\s*\.command\(`([^`]+)`(?:,\s*\{\s*hidden:\s*(true|false)\s*\})?\)/;
// `export const fooBarCommand = new Command(`name`)` — promoted root commands
const ROOT_COMMAND = /^export const (\w+) = new Command\(`([^`]+)`\)/;
// `export const service = new Command("name")`
const SERVICE_COMMAND = /^export const (\w+) = new Command\("([^"]+)"\)/;
const ALIAS = /^\s*\.alias\("([^"]+)"\)/;
// Option syntax is always the first argument and always backticked, but the
// call may wrap across lines when a custom parser follows.
const OPTION_START = /^\s*\.(option|requiredOption)\(\s*$/;
const OPTION_INLINE = /^\s*\.(option|requiredOption)\(`([^`]+)`/;
const BACKTICKED = /^\s*`([^`]+)`/;

/**
 * Split a commander option syntax string into its parts.
 *   `-f, --force`              → shorthand -f, name --force, no value
 *   `--database-id <id>`       → required value
 *   `--queries [queries...]`   → optional variadic value
 */
function parseOptionSyntax(syntax) {
  const [flagPart, valuePart] = syntax.split(/\s+(?=[<[])/);
  const flags = flagPart.split(',').map((f) => f.trim());
  const shorthand = flags.find((f) => /^-[^-]/.test(f)) ?? null;
  const name = flags.find((f) => f.startsWith('--')) ?? null;

  return {
    name,
    shorthand,
    valueRequired: valuePart?.startsWith('<') ?? false,
    valueOptional: valuePart?.startsWith('[') ?? false,
    variadic: valuePart?.includes('...') ?? false,
  };
}

function parseService(file) {
  const lines = fs.readFileSync(file, 'utf8').split('\n');
  const service = { name: null, aliases: [], commands: [] };
  let current = null;

  const flush = () => {
    if (current) {
      service.commands.push(current);
      current = null;
    }
  };

  for (let i = 0; i < lines.length; i++) {
    const line = lines[i];

    const serviceMatch = SERVICE_COMMAND.exec(line);
    if (serviceMatch) {
      flush();
      service.name = serviceMatch[2];
      // The service's own `.alias()` follows its declaration, before the first
      // subcommand — distinguish it from a subcommand alias by `current`.
      continue;
    }

    const rootMatch = ROOT_COMMAND.exec(line);
    if (rootMatch) {
      flush();
      current = { name: rootMatch[2], standalone: true, hidden: false, aliases: [], options: [] };
      continue;
    }

    const subMatch = SUBCOMMAND.exec(line);
    if (subMatch) {
      flush();
      current = {
        name: subMatch[1],
        standalone: false,
        hidden: subMatch[2] === 'true',
        aliases: [],
        options: [],
      };
      continue;
    }

    const aliasMatch = ALIAS.exec(line);
    if (aliasMatch) {
      (current ? current.aliases : service.aliases).push(aliasMatch[1]);
      continue;
    }

    if (!current) {
      continue;
    }

    const inline = OPTION_INLINE.exec(line);
    if (inline) {
      current.options.push({ required: inline[1] === 'requiredOption', ...parseOptionSyntax(inline[2]) });
      continue;
    }

    // Wrapped form: syntax sits on the next line.
    const wrapped = OPTION_START.exec(line);
    if (wrapped) {
      const next = BACKTICKED.exec(lines[i + 1] ?? '');
      if (next) {
        current.options.push({ required: wrapped[1] === 'requiredOption', ...parseOptionSyntax(next[1]) });
        i++;
      }
      continue;
    }
  }

  flush();
  return service;
}

const files = fs
  .readdirSync(servicesDir)
  .filter((f) => f.endsWith('.ts'))
  .sort();

const services = files.map((f) => parseService(path.join(servicesDir, f)));
const commandCount = services.reduce((sum, s) => sum + s.commands.length, 0);
const optionCount = services.reduce(
  (sum, s) => sum + s.commands.reduce((n, c) => n + c.options.length, 0),
  0,
);

fs.mkdirSync(path.dirname(outFile), { recursive: true });
fs.writeFileSync(
  outFile,
  JSON.stringify({ services, totals: { services: services.length, commands: commandCount, options: optionCount } }, null, 2) + '\n',
);

process.stdout.write(
  `${services.length} services, ${commandCount} commands, ${optionCount} options → ${outFile}\n`,
);
