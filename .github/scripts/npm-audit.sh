#!/usr/bin/env bash
set -euo pipefail

npm audit --audit-level=moderate "$@"

report=$(npm audit --json "$@" || true)
node -e '
const report = JSON.parse(process.argv[1]);
if (report.error || typeof report.vulnerabilities !== "object") {
    console.error("npm audit did not produce a report: " + JSON.stringify(report.error ?? report));
    process.exit(1);
}
const fixable = Object.values(report.vulnerabilities).filter(
    (vulnerability) => vulnerability.via.some((via) => typeof via === "object")
        && (vulnerability.fixAvailable === true
            || (typeof vulnerability.fixAvailable === "object" && !vulnerability.fixAvailable.isSemVerMajor)),
);
if (fixable.length > 0) {
    console.error("Fixable advisories: " + fixable.map((vulnerability) => vulnerability.name).join(", "));
    process.exit(1);
}
' "$report"
