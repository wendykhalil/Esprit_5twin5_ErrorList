#!/bin/sh
set -eu

MODEL="${OLLAMA_MODEL:-mistral:latest}"

i=0
until ollama list >/dev/null 2>&1; do
  i=$((i + 1))
  if [ "$i" -gt 60 ]; then
    echo "Ollama API did not become ready" >&2
    exit 1
  fi
  sleep 2
done

if ollama list | awk 'NR>1 {print $1}' | grep -Fxq "$MODEL"; then
  echo "Model already present: $MODEL"
  exit 0
fi

echo "Pulling model: $MODEL"
ollama pull "$MODEL"

if ollama list | awk 'NR>1 {print $1}' | grep -Fxq "$MODEL"; then
  echo "Model ready: $MODEL"
  exit 0
fi

echo "Model missing after pull: $MODEL" >&2
exit 1
