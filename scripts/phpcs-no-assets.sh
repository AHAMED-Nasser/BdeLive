#!/bin/bash
# PHPCS wrapper that excludes assets directory (JS/CSS files)
# This prevents PHPCS from analyzing JavaScript files which it's not designed for

vendor/bin/phpcs --standard=PSR12 \
    app/Core \
    app/Config \
    app/Modules \
    app/Services \
    app/include \
    app/index.php \
    app/rooter.php \
    --error-severity=1 \
    --warning-severity=0 \
    "$@"
