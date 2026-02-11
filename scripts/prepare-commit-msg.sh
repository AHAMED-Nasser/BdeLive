#!/bin/sh
# Git prepare-commit-msg hook: remove Cursor co-author line from commit message.
# Usage: pass the commit message file path as first argument (Git does this automatically).
if [ -n "$1" ] && [ -f "$1" ]; then
    sed -i '/^Co-authored-by: Cursor <cursoragent@cursor.com>$/d' "$1"
fi
