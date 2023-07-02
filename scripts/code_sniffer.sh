#!/bin/bash

# Check the operating system
if [[ "$OSTYPE" == "darwin"* ]]; then
    # macOS
    userPath="$HOME"
elif [[ "$OSTYPE" == "msys" || "$OSTYPE" == "cygwin" ]]; then
    # Windows
    userPath="$USERPROFILE"
else
    # Unsupported operating system
    echo "Unsupported operating system"
    exit 1
fi

composer global require "squizlabs/php_codesniffer=*"
composer global require magento/magento-coding-standard
composer global require "phpcompatibility/phpcompatibility-wp=*"

# Set installed_paths
phpcs --config-set installed_paths $userPath/.composer/vendor/phpcompatibility/php-compatibility,$userPath/.composer/vendor/magento/magento-coding-standard

# Run a test and check available packages
phpcs -i
