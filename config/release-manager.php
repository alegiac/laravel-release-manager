<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Default Initial Version
    |--------------------------------------------------------------------------
    |
    | The default version to use when initializing the release system.
    | This follows semantic versioning: MAJOR.MINOR.PATCH
    |
    */
    'default_version' => env('RELEASE_MANAGER_DEFAULT_VERSION', '0.0.1'),

    /*
    |--------------------------------------------------------------------------
    | Changelog File
    |--------------------------------------------------------------------------
    |
    | The path to the changelog file relative to the project root.
    |
    */
    'changelog_file' => env('RELEASE_MANAGER_CHANGELOG_FILE', 'CHANGELOG.md'),

    /*
    |--------------------------------------------------------------------------
    | Tag Prefix
    |--------------------------------------------------------------------------
    |
    | The prefix to use for git tags. Common values are 'v' or empty string.
    | Example: 'v' will create tags like v1.0.0, empty will create 1.0.0
    |
    */
    'tag_prefix' => env('RELEASE_MANAGER_TAG_PREFIX', 'v'),

    /*
    |--------------------------------------------------------------------------
    | Commit Message Template
    |--------------------------------------------------------------------------
    |
    | Template for the release commit message.
    | Available variables: {version}
    |
    */
    'commit_message' => env('RELEASE_MANAGER_COMMIT_MESSAGE', 'chore(release): {version}'),

    /*
    |--------------------------------------------------------------------------
    | Conventional Commit Types
    |--------------------------------------------------------------------------
    |
    | Mapping of conventional commit types to changelog sections.
    | You can customize these to match your workflow.
    |
    */
    'commit_types' => [
        'feat' => 'Features',
        'fix' => 'Bug Fixes',
        'docs' => 'Documentation',
        'style' => 'Styles',
        'refactor' => 'Code Refactoring',
        'perf' => 'Performance Improvements',
        'test' => 'Tests',
        'build' => 'Build System',
        'ci' => 'Continuous Integration',
        'chore' => 'Chores',
    ],

    /*
    |--------------------------------------------------------------------------
    | Versioning Rules
    |--------------------------------------------------------------------------
    |
    | Rules for automatic version bumping based on commit types.
    |
    */
    'versioning' => [
        'breaking_change_bumps_major' => true,
        'feat_bumps_minor' => true,
        'fix_bumps_patch' => true,
    ],
];

