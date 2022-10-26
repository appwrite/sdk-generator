#!/bin/sh
mix local.hex --force
mix local.rebar --force
mix deps.get
mix run /app/tests/languages/elixir/tests.exs
