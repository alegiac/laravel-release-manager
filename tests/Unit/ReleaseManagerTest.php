<?php

use Alegiac\ReleaseManager\Services\ReleaseManager;

describe('ReleaseManager', function () {
    it('analyzes conventional commits correctly', function () {
        $manager = new ReleaseManager();
        
        $commits = [
            'feat: add new feature',
            'fix: resolve bug',
            'docs: update README',
        ];

        $analysis = $manager->analyzeCommits($commits);

        expect($analysis)->toHaveKey('has_breaking');
        expect($analysis)->toHaveKey('has_feat');
        expect($analysis)->toHaveKey('has_fix');
        expect($analysis['has_feat'])->toBeTrue();
        expect($analysis['has_fix'])->toBeTrue();
        expect($analysis['has_breaking'])->toBeFalse();
    });

    it('detects breaking changes', function () {
        $manager = new ReleaseManager();
        
        $commits = [
            'feat!: breaking change',
        ];

        $analysis = $manager->analyzeCommits($commits);

        expect($analysis['has_breaking'])->toBeTrue();
    });

    it('categorizes commits by type', function () {
        $manager = new ReleaseManager();
        
        $commits = [
            'feat: add feature',
            'fix: fix bug',
            'docs: update docs',
        ];

        $analysis = $manager->analyzeCommits($commits);

        expect($analysis['by_type'])->toHaveKey('feat');
        expect($analysis['by_type'])->toHaveKey('fix');
        expect($analysis['by_type'])->toHaveKey('docs');
        expect($analysis['by_type']['feat'])->toContain('add feature');
        expect($analysis['by_type']['fix'])->toContain('fix bug');
    });

    it('determines major release for breaking changes', function () {
        $manager = new ReleaseManager();
        
        $analysis = [
            'has_breaking' => true,
            'has_feat' => false,
            'has_fix' => false,
        ];

        $releaseType = $manager->determineReleaseType($analysis);

        expect($releaseType)->toBe('major');
    });

    it('determines minor release for features', function () {
        $manager = new ReleaseManager();
        
        $analysis = [
            'has_breaking' => false,
            'has_feat' => true,
            'has_fix' => false,
        ];

        $releaseType = $manager->determineReleaseType($analysis);

        expect($releaseType)->toBe('minor');
    });

    it('determines patch release for fixes', function () {
        $manager = new ReleaseManager();
        
        $analysis = [
            'has_breaking' => false,
            'has_feat' => false,
            'has_fix' => true,
        ];

        $releaseType = $manager->determineReleaseType($analysis);

        expect($releaseType)->toBe('patch');
    });

    it('generates changelog correctly', function () {
        $manager = new ReleaseManager();
        
        $analysis = [
            'by_type' => [
                'feat' => ['add new feature'],
                'fix' => ['resolve bug'],
            ],
        ];

        $changelog = $manager->generateChangelog('v1.0.0', $analysis);

        expect($changelog)->toContain('## [v1.0.0]');
        expect($changelog)->toContain('### Features');
        expect($changelog)->toContain('add new feature');
        expect($changelog)->toContain('### Bug Fixes');
        expect($changelog)->toContain('resolve bug');
    });

    it('handles commits with scope', function () {
        $manager = new ReleaseManager();
        
        $commits = [
            'feat(api): add new endpoint',
            'fix(ui): resolve button issue',
        ];

        $analysis = $manager->analyzeCommits($commits);

        expect($analysis['by_type']['feat'])->toContain('add new endpoint');
        expect($analysis['by_type']['fix'])->toContain('resolve button issue');
    });

    it('handles non-conventional commits', function () {
        $manager = new ReleaseManager();
        
        $commits = [
            'feat: add feature',
            'random commit message',
            'another update',
        ];

        $analysis = $manager->analyzeCommits($commits);

        expect($analysis['by_type'])->toHaveKey('other');
        expect($analysis['by_type']['other'])->toContain('random commit message');
        expect($analysis['by_type']['other'])->toContain('another update');
    });
});

