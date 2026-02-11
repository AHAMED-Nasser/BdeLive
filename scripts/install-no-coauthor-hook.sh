#!/bin/bash
# Install prepare-commit-msg hook to strip Cursor co-author from commit messages.
set -e
REPO_ROOT="$(git rev-parse --show-toplevel)"
HOOK_SRC="${REPO_ROOT}/scripts/prepare-commit-msg.sh"
HOOK_DST="${REPO_ROOT}/.git/hooks/prepare-commit-msg"
if [ -f "$HOOK_SRC" ]; then
    cp "$HOOK_SRC" "$HOOK_DST"
    chmod +x "$HOOK_DST"
    echo "Hook installé: .git/hooks/prepare-commit-msg (supprime le co-auteur Cursor)"
else
    echo "Fichier source introuvable: $HOOK_SRC"
    exit 1
fi
