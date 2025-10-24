#!/bin/bash

# Script per inizializzare il sistema di release
# Uso: ./release-setup.sh [version]

set -e

# Colori per output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# Funzione per stampare messaggi colorati
print_info() {
    echo -e "${GREEN}[INFO]${NC} $1"
}

print_error() {
    echo -e "${RED}[ERROR]${NC} $1"
}

print_warning() {
    echo -e "${YELLOW}[WARNING]${NC} $1"
}

print_section() {
    echo -e "${BLUE}[SECTION]${NC} $1"
}

print_section "Inizializzazione Sistema Auto Release"
echo ""

# Versione iniziale (default: 0.0.1)
INITIAL_VERSION=${1:-0.0.1}

# Valida formato versione
if [[ ! $INITIAL_VERSION =~ ^[0-9]+\.[0-9]+\.[0-9]+$ ]]; then
    print_error "Formato versione non valido: $INITIAL_VERSION"
    print_info "La versione deve essere in formato: MAJOR.MINOR.PATCH (es. 0.0.1, 1.0.0)"
    exit 1
fi

print_info "Versione iniziale: v$INITIAL_VERSION"

# Controlla se già inizializzato
if [[ -f CHANGELOG.md ]] || [[ -n $(git tag -l) ]]; then
    print_warning "Sistema già inizializzato:"
    
    if [[ -f CHANGELOG.md ]]; then
        echo "  - CHANGELOG.md esiste"
    fi
    
    if [[ -n $(git tag -l) ]]; then
        EXISTING_TAGS=$(git tag -l | head -3 | tr '\n' ' ')
        echo "  - Tag git esistenti: $EXISTING_TAGS"
    fi
    
    echo ""
    read -p "Vuoi continuare e reinizializzare? (y/n) " -n 1 -r
    echo
    if [[ ! $REPLY =~ ^[Yy]$ ]]; then
        print_info "Setup annullato"
        exit 0
    fi
fi

# Crea CHANGELOG.md
print_info "Creazione CHANGELOG.md..."

DATE=$(date +"%Y-%m-%d")

cat > CHANGELOG.md << EOF
# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [v$INITIAL_VERSION] - $DATE

### Features

- Initial release
EOF

print_info "✓ CHANGELOG.md creato"

# Commit CHANGELOG.md se necessario
if [[ -n $(git status --porcelain CHANGELOG.md) ]]; then
    git add CHANGELOG.md
    git commit -m "chore: initialize CHANGELOG for v$INITIAL_VERSION"
    print_info "✓ CHANGELOG.md committato"
fi

# Crea tag iniziale
TAG_NAME="v$INITIAL_VERSION"

# Controlla se il tag esiste già
if git tag -l | grep -q "^$TAG_NAME$"; then
    print_warning "Tag $TAG_NAME già esistente"
    read -p "Vuoi eliminarlo e ricrearlo? (y/n) " -n 1 -r
    echo
    if [[ $REPLY =~ ^[Yy]$ ]]; then
        git tag -d "$TAG_NAME"
        print_info "✓ Tag esistente eliminato"
    else
        print_info "Setup completato (tag non ricreato)"
        exit 0
    fi
fi

# Crea tag annotato
TAG_MESSAGE="Release $TAG_NAME

## [$TAG_NAME] - $DATE

### Features
- Initial release"

git tag -a "$TAG_NAME" -m "$TAG_MESSAGE"
print_info "✓ Tag $TAG_NAME creato"

echo ""
print_section "Sistema Auto Release inizializzato con successo!"
echo ""

print_info "Versione iniziale: $TAG_NAME"
print_info "CHANGELOG.md: creato"
print_info "Git tag: creato"
echo ""

print_section "Prossimi passi:"
echo ""
echo "1. Fai le tue modifiche e committa usando Conventional Commits:"
echo "   git commit -m \"feat: add new feature\""
echo ""
echo "2. Crea una release:"
echo "   ./release-conventional.sh auto"
echo ""
echo "3. Pubblica:"
echo "   git push --follow-tags"
echo ""

print_info "Per maggiori informazioni, consulta RELEASING.md"

