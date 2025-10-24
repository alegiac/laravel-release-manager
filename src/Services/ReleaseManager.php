<?php

namespace Alegiac\ReleaseManager\Services;

/**
 * Release Manager
 * 
 * Handles analysis of commits and changelog generation.
 * 
 * @package Alegiac\ReleaseManager\Services
 */
class ReleaseManager
{
    /**
     * Commit types mapping
     *
     * @var array<string, string>
     */
    protected array $commitTypes = [
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
    ];

    /**
     * Analyze commits and categorize them
     *
     * @param array $commits
     * @return array
     */
    public function analyzeCommits(array $commits): array
    {
        $analysis = [
            'has_breaking' => false,
            'has_feat' => false,
            'has_fix' => false,
            'by_type' => [],
        ];

        foreach ($commits as $commit) {
            // Match conventional commit format: type(scope): message
            if (preg_match('/^([a-z]+)(\([a-z-]+\))?(!)?:\s+(.+)$/i', $commit, $matches)) {
                $type = strtolower($matches[1]);
                $scope = $matches[2] ?? '';
                $breaking = !empty($matches[3]);
                $message = $matches[4];

                if ($breaking) {
                    $analysis['has_breaking'] = true;
                }

                if ($type === 'feat') {
                    $analysis['has_feat'] = true;
                } elseif ($type === 'fix') {
                    $analysis['has_fix'] = true;
                }

                if (isset($this->commitTypes[$type])) {
                    if (!isset($analysis['by_type'][$type])) {
                        $analysis['by_type'][$type] = [];
                    }
                    $analysis['by_type'][$type][] = $message . $scope;
                } else {
                    if (!isset($analysis['by_type']['other'])) {
                        $analysis['by_type']['other'] = [];
                    }
                    $analysis['by_type']['other'][] = $commit;
                }
            } else {
                // Non-conventional commit
                if (!isset($analysis['by_type']['other'])) {
                    $analysis['by_type']['other'] = [];
                }
                $analysis['by_type']['other'][] = $commit;
            }
        }

        return $analysis;
    }

    /**
     * Determine release type from analysis
     *
     * @param array $analysis
     * @return string
     */
    public function determineReleaseType(array $analysis): string
    {
        if ($analysis['has_breaking']) {
            return 'major';
        }

        if ($analysis['has_feat']) {
            return 'minor';
        }

        return 'patch';
    }

    /**
     * Generate changelog entry
     *
     * @param string $version
     * @param array $analysis
     * @return string
     */
    public function generateChangelog(string $version, array $analysis): string
    {
        $date = date('Y-m-d');
        $changelog = "## [{$version}] - {$date}\n";

        foreach ($this->commitTypes as $type => $label) {
            if (isset($analysis['by_type'][$type]) && !empty($analysis['by_type'][$type])) {
                $changelog .= "\n### {$label}\n\n";
                foreach ($analysis['by_type'][$type] as $message) {
                    $changelog .= "- {$message}\n";
                }
            }
        }

        if (isset($analysis['by_type']['other']) && !empty($analysis['by_type']['other'])) {
            $changelog .= "\n### Other Changes\n\n";
            foreach ($analysis['by_type']['other'] as $message) {
                $changelog .= "- {$message}\n";
            }
        }

        return $changelog;
    }

    /**
     * Update CHANGELOG.md file
     *
     * @param string $changelogEntry
     * @return void
     */
    public function updateChangelog(string $changelogEntry): void
    {
        $changelogPath = base_path('CHANGELOG.md');

        if (file_exists($changelogPath)) {
            $existingChangelog = file_get_contents($changelogPath);
            
            // Insert after the header (first 2 lines)
            $lines = explode("\n", $existingChangelog);
            $header = array_slice($lines, 0, 2);
            $body = array_slice($lines, 2);
            
            $newContent = implode("\n", $header) . "\n\n" . $changelogEntry . "\n" . implode("\n", $body);
            
            file_put_contents($changelogPath, $newContent);
        } else {
            // Create new CHANGELOG.md
            $content = "# Changelog\n\n";
            $content .= "All notable changes to this project will be documented in this file.\n\n";
            $content .= "The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),\n";
            $content .= "and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).\n\n";
            $content .= $changelogEntry;
            
            file_put_contents($changelogPath, $content);
        }
    }
}

