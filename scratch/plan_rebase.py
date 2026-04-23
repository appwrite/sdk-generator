
import os
import subprocess

# The list of commits from oldest to newest (bottom up from the previous git log)
commits = [
    "26b2a7577", # 0: feat(cpp): implement C++ SDK generator class and type mapping
    "54f141e54", # 1: feat(cpp): add base templates and project structure
    "a15a3f667", # 2: feat(cpp): implement Query, Permission, and ID helpers
    "03fec1d41", # 3: feat(cpp): implement monolithic model generation with topological sorting
    "b8b80d08c", # 4: feat(cpp): implement monolithic service generation and dispatch
    "1036fa513", # 5: feat(cpp): implement Task<T> awaitable protocol and thread-pool
    "b126927f3", # 6: feat(cpp): implement monolithic enum generation with ADL JSON hooks
    "52aca1b43", # 7: test(cpp): implement comprehensive mock-API integration suite
    "9a974aa2a", # 8: test(cpp): add gtest-based unit tests for models and helpers
    "970323761", # 9: docs(cpp): add README, usage examples, and publish workflows
    "f369a9669", # 10: fix(ci): restore pinned Bun version and Darwin signature verification
    "4f1e74535", # 11: chore(cpp): generate production SDK output (examples/cpp)
    "0d6baa58d", # 12: fix(cpp): make integration tests compilable and linkable against mock API
    "e72fa8dec", # 13: fix(cpp): remove duplicate 'file' field from upload multipart body
    "205756512", # 14: fix(cpp): rewrite integration_logic to match mock API contract
    "c511b00b9", # 15: fix(cpp): remove invalid std::get<exception_ptr> on non-variant Result type
    "3d635854e", # 16: fix(cpp): use raw response body as error message for non-JSON error responses
    "f92e0a200", # 17: fix(cpp): ID::unique() generates local 20-char sequence
    "bd0d7c709", # 18: fix(cpp): add missing <format> include to services module
    "97f53e6fa", # 19: fix(cpp): fix array query parameters and integration mode outputs
    "9d2805dfa", # 20: style(cpp): fix phpcs inline control structures in Cpp generator class
    "9552366e1", # 21: feat: hardening C++ SDK generator and fixing integration tests
    "b7f8280b3", # 22: feat: improve C++ SDK deserialization, documentation, and entry point
    "f03a3d4b6", # 23: feat: harden C++ SDK client with C++20 designated initializers and enhanced error handling
    "1f12823b2", # 24: feat: finalize C++ SDK hardening with correct integration output order and exception safety
    "74fbfa2ca", # 25: chore: update .gitignore and sanitize tracked files
    "0a6c5bfcf", # 26: feat: harden CI environment and restore production client architecture
    "80b900d89", # 27: chore: track missing appwrite.hpp entry point and finalize production readiness
    "3cede6e10", # 28: fix(cpp): resolve 502 handler duplication, endpoint path, and progress dispatch
    "25ca9527e", # 29: chore: regenerate examples/cpp with base.hpp error() fix
    "f24d144d8", # 30: feat(cpp): harden realtime service and integration tests
    "3d1564a13", # 31: fix(cpp): limit retries to connection failures and align test headers
    "33cca3f45", # 32: chore(cpp): regenerate SDK from updated templates
    "a3b872974", # 33: fix(cpp): resolve P1 hardening issues and stabilize CI dependencies
    "9f0492035", # 34: feat(cpp): finalize production hardening and integrate Greptile feedback
    "546a552c5", # 35: feat(cpp): harden callLocation error handling and align with verify()
    "982469ca4", # 36: feat(cpp): final absolute production hardening and architectural alignment
    "bd9a69dbf", # 37: feat(cpp): harden SDK — polymorphic exceptions, qualified BinaryResponse, and robust integration test reporting
    "cf8d740f3", # 38: feat(cpp): implement Option A for SDK headers and optimize CMake dependency fetching
    "1b214745b", # 39: fix(cpp): synchronize CMake FetchContent tags with CI Dockerfile pinned commits
    "ba19e2721", # 40: ci(cpp): pre-install cpr in CI workflow and finalize build validation
]

# Define Groups (indices in the commits list)
groups = {
    "01 Foundation & Base Templates": [0, 1],
    "02 Query, Permission, & ID Helpers": [2, 17, 19],
    "03 Models, Services, & Enums": [3, 4, 6, 18, 20],
    "04 Async Protocol & Transport (Task/ThreadPool)": [5],
    "05 Test Harness & Integration Suite": [7, 8, 12, 14, 21, 24, 30, 31],
    "06 Documentation & CI Workflows": [9, 10, 25, 26, 33, 40],
    "07 Transport Hardening & Multipart": [13, 16, 23, 35],
    "08 Exception Safety & Error Models": [15, 37],
    "09 Deserialization & Logical Hardening": [22, 28, 34, 36],
    "10 SDK Production Entry Point & Final Regeneration": [11, 27, 29, 32, 38, 39]
}

# Create a mapping from commit hash to rebase action
# We'll pick the first in each group and squash the rest.
action_map = {}
for name, indices in groups.items():
    first = True
    sorted_indices = sorted(indices)
    for idx in sorted_indices:
        commit_hash = commits[idx]
        if first:
            action_map[commit_hash] = ("pick", name)
            first = False
        else:
            action_map[commit_hash] = ("squash", name)

# The remaining commits not in any group will be squashed into the nearest preceding group.
# (But I've tried to cover all). Let's verify all are covered.
covered = set()
for indices in groups.values():
    covered.update(indices)
all_indices = set(range(len(commits)))
missing = all_indices - covered
if missing:
    print(f"Missing indices: {missing}")
    # Just fixup those into the last defined group for safety
    for idx in missing:
        action_map[commits[idx]] = ("fixup", "Remaining Cleanup")

# Generate the rebase script
script_lines = []
for c in commits:
    action, label = action_map[c]
    # We use a placeholder for the message since squash will combine them
    script_lines.append(f"{action} {c}")

with open("rebase_todo.txt", "w") as f:
    f.write("\n".join(script_lines))

# Generate a script that replaces the message for the new squashed commits
# After squash, git will open an editor to combine messages.
# We want to use the names in 'groups' as the new commit messages.
# This part is best done manually or by preparing the message file.

print("Rebase TODO generated in rebase_todo.txt")
