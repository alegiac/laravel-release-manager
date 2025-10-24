#!/bin/bash

# Script per creare una nuova release con changelog da Conventional Commits
# Uso: ./release-conventional.sh [patch|minor|major|auto]

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

# Verifica che il repository sia pulito
if [[ -n $(git status -s) ]]; then
    print_error "Repository non pulito. Committa o stash le modifiche prima di creare una release."
    exit 1
fi

# Ottieni l'ultimo tag
LATEST_TAG=$(git describe --tags --abbrev=0 2>/dev/null || echo "v0.0.0")
print_info "Ultimo tag: $LATEST_TAG"

# Rimuovi la 'v' iniziale se presente
VERSION=${LATEST_TAG#v}

# Dividi la versione in major.minor.patch
IFS='.' read -r -a VERSION_PARTS <<< "$VERSION"
MAJOR=${VERSION_PARTS[0]:-0}
MINOR=${VERSION_PARTS[1]:-0}
PATCH=${VERSION_PARTS[2]:-0}

# Ottieni tutti i commit dall'ultimo tag
COMMITS=$(git log ${LATEST_TAG}..HEAD --pretty=format:"%s")

if [[ -z "$COMMITS" ]]; then
    print_error "Nessun commit trovato dall'ultimo tag."
    exit 1
fi

# Analizza i commit per tipo (Conventional Commits)
declare -A COMMIT_TYPES
COMMIT_TYPES=(
    ["feat"]="Features"
    ["fix"]="Bug Fixes"
    ["docs"]="Documentation"
    ["style"]="Styles"
    ["refactor"]="Code Refactoring"
    ["perf"]="Performance Improvements"
    ["test"]="Tests"
    ["build"]="Build System"
    ["ci"]="Continuous Integration"
    ["chore"]="Chores"
)

# Array per memorizzare i commit per tipo
declare -A COMMITS_BY_TYPE
HAS_BREAKING=false
HAS_FEAT=false
HAS_FIX=false

# Analizza ogni commit
while IFS= read -r commit; do
    # Estrai il tipo di commit
    if [[ $commit =~ ^([a-z]+)(\([a-z-]+\))?(!)?:\ (.+)$ ]]; then
        TYPE="${BASH_REMATCH[1]}"
        SCOPE="${BASH_REMATCH[2]}"
        BREAKING="${BASH_REMATCH[3]}"
        MESSAGE="${BASH_REMATCH[4]}"
        
        # Controlla se è un breaking change
        if [[ -n "$BREAKING" ]]; then
            HAS_BREAKING=true
        fi
        
        # Traccia feat e fix per auto-versioning
        if [[ "$TYPE" == "feat" ]]; then
            HAS_FEAT=true
        elif [[ "$TYPE" == "fix" ]]; then
            HAS_FIX=true
        fi
        
        # Aggiungi il commit all'array appropriato
        if [[ -n "${COMMIT_TYPES[$TYPE]}" ]]; then
            COMMITS_BY_TYPE[$TYPE]+="- $MESSAGE$SCOPE"$'\n'
        else
            COMMITS_BY_TYPE["other"]+="- $commit"$'\n'
        fi
    else
        # Commit non conventional
        COMMITS_BY_TYPE["other"]+="- $commit"$'\n'
    fi
done <<< "$COMMITS"

# Determina il tipo di release
RELEASE_TYPE=${1:-auto}

if [[ "$RELEASE_TYPE" == "auto" ]]; then
    if [[ "$HAS_BREAKING" == true ]]; then
        RELEASE_TYPE="major"
        print_info "Rilevato breaking change - incremento MAJOR"
    elif [[ "$HAS_FEAT" == true ]]; then
        RELEASE_TYPE="minor"
        print_info "Rilevate nuove feature - incremento MINOR"
    elif [[ "$HAS_FIX" == true ]]; then
        RELEASE_TYPE="patch"
        print_info "Rilevati solo fix - incremento PATCH"
    else
        RELEASE_TYPE="patch"
        print_info "Nessun feat/fix rilevato - incremento PATCH di default"
    fi
fi

# Incrementa la versione
case $RELEASE_TYPE in
    major)
        MAJOR=$((MAJOR + 1))
        MINOR=0
        PATCH=0
        ;;
    minor)
        MINOR=$((MINOR + 1))
        PATCH=0
        ;;
    patch)
        PATCH=$((PATCH + 1))
        ;;
    *)
        print_error "Tipo di release non valido. Usa: patch, minor, major, o auto"
        exit 1
        ;;
esac

NEW_VERSION="v$MAJOR.$MINOR.$PATCH"
print_info "Nuova versione: $NEW_VERSION"

# Genera il changelog
print_section "Generazione changelog..."

DATE=$(date +"%Y-%m-%d")

# Costruisci l'entry del changelog
CHANGELOG_ENTRY="## [$NEW_VERSION] - $DATE
"

# Aggiungi i commit per tipo
for type in "${!COMMIT_TYPES[@]}"; do
    if [[ -n "${COMMITS_BY_TYPE[$type]}" ]]; then
        CHANGELOG_ENTRY+="
### ${COMMIT_TYPES[$type]}

${COMMITS_BY_TYPE[$type]}"
    fi
done

# Aggiungi altri commit se presenti
if [[ -n "${COMMITS_BY_TYPE[other]}" ]]; then
    CHANGELOG_ENTRY+="
### Other Changes

${COMMITS_BY_TYPE[other]}"
fi

CHANGELOG_ENTRY+="
"

# Aggiorna CHANGELOG.md
if [[ -f CHANGELOG.md ]]; then
    cp CHANGELOG.md CHANGELOG.md.bak
    
    {
        head -n 2 CHANGELOG.md
        echo ""
        echo "$CHANGELOG_ENTRY"
        tail -n +3 CHANGELOG.md
    } > CHANGELOG.md.tmp
    
    mv CHANGELOG.md.tmp CHANGELOG.md
    rm CHANGELOG.md.bak
else
    echo "# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

$CHANGELOG_ENTRY" > CHANGELOG.md
fi

print_info "Changelog aggiornato"

# Mostra il changelog
print_section "Nuovo entry nel changelog:"
echo "$CHANGELOG_ENTRY"

# Chiedi conferma
echo ""
read -p "Vuoi procedere con la release $NEW_VERSION? (y/n) " -n 1 -r
echo
if [[ ! $REPLY =~ ^[Yy]$ ]]; then
    print_warning "Release annullata"
    git checkout CHANGELOG.md
    exit 1
fi

# Commit del changelog
print_info "Commit del changelog..."
git add CHANGELOG.md
git commit -m "chore(release): $NEW_VERSION"

# Crea il tag
print_info "Creazione tag $NEW_VERSION..."
git tag -a "$NEW_VERSION" -m "Release $NEW_VERSION

$CHANGELOG_ENTRY"

print_section "Release $NEW_VERSION creata con successo!"
echo ""
print_info "Per pubblicare, esegui:"
echo "  git push origin $(git rev-parse --abbrev-ref HEAD)"
echo "  git push origin $NEW_VERSION"
echo ""
print_info "Oppure usa: git push --follow-tags"

