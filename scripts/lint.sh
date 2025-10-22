#!/bin/bash

# Script de linting pour BdeLive
# Usage: ./scripts/lint.sh [phpstan|phpcs|php-cs-fixer|all]

set -e

PROJECT_ROOT="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
cd "$PROJECT_ROOT"

# Couleurs pour l'affichage
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

echo -e "${YELLOW}🔍 Linting BdeLive Project${NC}"
echo "================================"

# Fonction pour exécuter PHPStan
run_phpstan() {
    echo -e "\n${YELLOW}📊 Running PHPStan (Static Analysis)${NC}"
    echo "--------------------------------"
    if php vendor/bin/phpstan analyse --memory-limit=1G; then
        echo -e "${GREEN}✅ PHPStan passed${NC}"
        return 0
    else
        echo -e "${RED}❌ PHPStan failed${NC}"
        return 1
    fi
}

# Fonction pour exécuter PHP CodeSniffer
run_phpcs() {
    echo -e "\n${YELLOW}📏 Running PHP CodeSniffer (Code Standards)${NC}"
    echo "----------------------------------------"
    if php vendor/bin/phpcs; then
        echo -e "${GREEN}✅ PHP CodeSniffer passed${NC}"
        return 0
    else
        echo -e "${RED}❌ PHP CodeSniffer failed${NC}"
        return 1
    fi
}

# Fonction pour exécuter PHP CS Fixer (dry-run)
run_php_cs_fixer() {
    echo -e "\n${YELLOW}🔧 Running PHP CS Fixer (Code Style Check)${NC}"
    echo "----------------------------------------"
    if php vendor/bin/php-cs-fixer fix --dry-run --diff; then
        echo -e "${GREEN}✅ PHP CS Fixer passed${NC}"
        return 0
    else
        echo -e "${RED}❌ PHP CS Fixer found issues${NC}"
        return 1
    fi
}

# Fonction pour corriger automatiquement avec PHP CS Fixer
fix_code() {
    echo -e "\n${YELLOW}🔧 Fixing code with PHP CS Fixer${NC}"
    echo "--------------------------------"
    if php vendor/bin/php-cs-fixer fix --diff; then
        echo -e "${GREEN}✅ Code fixed successfully${NC}"
        return 0
    else
        echo -e "${RED}❌ Code fixing failed${NC}"
        return 1
    fi
}

# Gestion des arguments
case "${1:-all}" in
    "phpstan")
        run_phpstan
        ;;
    "phpcs")
        run_phpcs
        ;;
    "php-cs-fixer")
        run_php_cs_fixer
        ;;
    "fix")
        fix_code
        ;;
    "all")
        phpstan_exit=0
        phpcs_exit=0
        cs_fixer_exit=0
        
        run_phpstan || phpstan_exit=1
        # Temporarily disabled due to PHP 8.4 compatibility issues
        # run_phpcs || phpcs_exit=1
        run_php_cs_fixer || cs_fixer_exit=1
        
        echo -e "\n${YELLOW}📋 Summary${NC}"
        echo "=========="
        
        if [ $phpstan_exit -eq 0 ]; then
            echo -e "${GREEN}✅ PHPStan: PASSED${NC}"
        else
            echo -e "${RED}❌ PHPStan: FAILED${NC}"
        fi
        
        echo -e "${YELLOW}⚠️  PHP CodeSniffer: DISABLED (PHP 8.4 compatibility)${NC}"
        
        if [ $cs_fixer_exit -eq 0 ]; then
            echo -e "${GREEN}✅ PHP CS Fixer: PASSED${NC}"
        else
            echo -e "${RED}❌ PHP CS Fixer: FAILED${NC}"
        fi
        
        if [ $phpstan_exit -eq 0 ] && [ $cs_fixer_exit -eq 0 ]; then
            echo -e "\n${GREEN}🎉 All linters passed!${NC}"
            exit 0
        else
            echo -e "\n${RED}💥 Some linters failed!${NC}"
            exit 1
        fi
        ;;
    *)
        echo "Usage: $0 [phpstan|phpcs|php-cs-fixer|fix|all]"
        echo ""
        echo "Commands:"
        echo "  phpstan      Run static analysis with PHPStan"
        echo "  phpcs        Check code standards with PHP CodeSniffer"
        echo "  php-cs-fixer Check code style with PHP CS Fixer"
        echo "  fix          Auto-fix code style issues"
        echo "  all          Run all linters (default)"
        exit 1
        ;;
esac
