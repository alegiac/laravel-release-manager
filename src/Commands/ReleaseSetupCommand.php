<?php

namespace Alegiac\ReleaseManager\Commands;

use Illuminate\Console\Command;
use Alegiac\ReleaseManager\Services\GitManager;

/**
 * Release Setup Command
 * 
 * Initializes the release management system with initial version and CHANGELOG.
 * 
 * @package Alegiac\ReleaseManager\Commands
 */
class ReleaseSetupCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'release:setup 
                            {--version=0.0.1 : Initial version to start from}
                            {--repo= : Repository URL (GitHub/Bitbucket)}
                            {--create-repo : Create repository on GitHub (requires gh CLI)}
                            {--workspace= : Bitbucket workspace for repo creation}
                            {--private : Make repository private (default: public)}
                            {--branches : Setup standard branches (main, develop, stage)}
                            {--push : Push branches and tags to remote}
                            {--force : Overwrite existing setup}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Initialize release management system with initial version';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle(): int
    {
        $this->info('🔧 Setting up Release Manager');
        $this->line('');

        $gitManager = new GitManager();

        // Initialize git repository if needed
        if (!$gitManager->isGitRepository()) {
            $this->warn('Git repository not initialized');
            
            if ($this->confirm('Initialize git repository?', true)) {
                if ($gitManager->initRepository()) {
                    $this->info('✓ Git repository initialized');
                } else {
                    $this->error('Failed to initialize git repository');
                    return Command::FAILURE;
                }
            } else {
                $this->error('Git repository required for release management');
                return Command::FAILURE;
            }
        }

        // Check if already initialized
        $changelogExists = file_exists(base_path('CHANGELOG.md'));
        exec('git tag -l', $tags);
        $hasTag = !empty($tags);

        if (($changelogExists || $hasTag) && !$this->option('force')) {
            $this->warn('Release system already initialized:');
            if ($changelogExists) {
                $this->line('  - CHANGELOG.md exists');
            }
            if ($hasTag) {
                $this->line('  - Git tags exist: ' . implode(', ', array_slice($tags, 0, 3)));
            }
            $this->line('');
            
            if (!$this->confirm('Do you want to continue and reinitialize?', false)) {
                $this->info('Setup cancelled');
                return Command::SUCCESS;
            }
        }

        // Get initial version
        $version = $this->option('version');
        
        // Validate version format
        if (!preg_match('/^\d+\.\d+\.\d+$/', $version)) {
            $this->error("Invalid version format: {$version}");
            $this->info('Version must be in format: MAJOR.MINOR.PATCH (e.g., 0.0.1, 1.0.0)');
            return Command::FAILURE;
        }

        $this->info("Initial version: v{$version}");
        $this->line('');

        // Create or update CHANGELOG.md
        $this->createChangelog($version);
        $this->info('✓ CHANGELOG.md created');

        // Create initial git tag
        $this->createInitialTag($version);
        $this->info('✓ Git tag created');

        // Publish scripts if not already done
        $scriptPath = base_path('release-conventional.sh');
        if (!file_exists($scriptPath)) {
            $this->call('vendor:publish', [
                '--tag' => 'release-manager-scripts',
                '--force' => $this->option('force')
            ]);
            
            // Make script executable
            chmod($scriptPath, 0755);
            $this->info('✓ Release scripts published');
        }

        // Publish documentation if not already done
        $releasingPath = base_path('RELEASING.md');
        if (!file_exists($releasingPath) || $this->option('force')) {
            $this->call('vendor:publish', [
                '--tag' => 'release-manager-docs',
                '--force' => $this->option('force')
            ]);
            $this->info('✓ Documentation published');
        }

        $this->line('');
        $this->info('🎉 Release System initialized successfully!');
        $this->line('');

        // Setup branches if requested
        if ($this->option('branches')) {
            $this->info('📋 Setting up standard branches...');
            $results = $gitManager->setupStandardBranches();
            
            foreach ($results as $branch => $created) {
                if ($created) {
                    $this->info("✓ Branch '{$branch}' created");
                } else {
                    $this->line("  Branch '{$branch}' already exists");
                }
            }
            $this->line('');
        }

        // Handle repository creation or linking
        if ($this->option('create-repo')) {
            $this->handleRepositoryCreation($gitManager);
        } elseif ($this->option('repo')) {
            $this->handleRepositoryLink($gitManager);
        }

        // Push if requested
        if ($this->option('push')) {
            $this->handlePush($gitManager);
        }

        $this->line('');
        $this->comment('Next steps:');
        
        if (!$this->option('repo') && !$this->option('create-repo')) {
            $this->line('1. Link your repository:');
            $this->line('   git remote add origin <repository-url>');
            $this->line('');
        }
        
        $this->line('2. Make your changes and commit using Conventional Commits:');
        $this->line('   git commit -m "feat: add new feature"');
        $this->line('');
        $this->line('3. Create a release:');
        $this->line('   php artisan release');
        $this->line('');
        $this->line('4. Push to repository:');
        $this->line('   git push --follow-tags');
        $this->line('');
        $this->comment('For more information, see RELEASING.md');

        return Command::SUCCESS;
    }

    /**
     * Handle repository creation
     *
     * @param GitManager $gitManager
     * @return void
     */
    protected function handleRepositoryCreation(GitManager $gitManager): void
    {
        $this->info('🔗 Creating repository...');
        
        $repoName = $this->ask('Repository name', basename(base_path()));
        $platform = $this->choice('Platform', ['GitHub', 'Bitbucket'], 'GitHub');
        
        if ($platform === 'GitHub') {
            $result = $gitManager->createGitHubRepository(
                $repoName, 
                $this->option('private')
            );
            
            if ($result['success']) {
                $this->info("✓ GitHub repository created: {$result['url']}");
            } else {
                $this->error("Failed to create repository: {$result['message']}");
            }
        } else {
            $workspace = $this->option('workspace') ?? $this->ask('Bitbucket workspace');
            $result = $gitManager->createBitbucketRepository(
                $workspace,
                $repoName,
                $this->option('private')
            );
            
            $this->warn($result['message']);
            
            if ($this->confirm('Have you created the repository manually?')) {
                $gitManager->addRemote($result['url']);
                $this->info('✓ Remote added');
            }
        }
        
        $this->line('');
    }

    /**
     * Handle repository linking
     *
     * @param GitManager $gitManager
     * @return void
     */
    protected function handleRepositoryLink(GitManager $gitManager): void
    {
        $this->info('🔗 Linking repository...');
        
        $repoUrl = $this->option('repo');
        
        if ($gitManager->addRemote($repoUrl)) {
            $this->info("✓ Remote 'origin' configured: {$repoUrl}");
        } else {
            $this->error('Failed to add remote');
        }
        
        $this->line('');
    }

    /**
     * Handle pushing to remote
     *
     * @param GitManager $gitManager
     * @return void
     */
    protected function handlePush(GitManager $gitManager): void
    {
        $this->info('📤 Pushing to remote...');
        
        // Get branches to push
        $branches = $this->option('branches') 
            ? ['main', 'develop', 'stage']
            : [$gitManager->getCurrentBranch()];
        
        $results = $gitManager->pushBranches($branches);
        
        foreach ($results as $branch => $success) {
            if ($success) {
                $this->info("✓ Branch '{$branch}' pushed");
            } else {
                $this->error("✗ Failed to push branch '{$branch}'");
            }
        }
        
        // Push tags
        if ($gitManager->pushTags()) {
            $this->info('✓ Tags pushed');
        } else {
            $this->warn('⚠ Failed to push tags');
        }
        
        $this->line('');
    }

    /**
     * Create initial CHANGELOG.md
     *
     * @param string $version
     * @return void
     */
    protected function createChangelog(string $version): void
    {
        $date = date('Y-m-d');
        
        $content = "# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [v{$version}] - {$date}

### Features

- Initial release
";

        file_put_contents(base_path('CHANGELOG.md'), $content);
    }

    /**
     * Create initial git tag
     *
     * @param string $version
     * @return void
     */
    protected function createInitialTag(string $version): void
    {
        $tagName = "v{$version}";
        $date = date('Y-m-d');
        
        $message = "Release {$tagName}

## [{$tagName}] - {$date}

### Features
- Initial release";

        // Check if tag already exists
        exec("git tag -l {$tagName}", $existingTag);
        
        if (!empty($existingTag)) {
            if ($this->option('force')) {
                exec("git tag -d {$tagName}");
                $this->warn("Existing tag {$tagName} deleted");
            } else {
                $this->warn("Tag {$tagName} already exists, skipping tag creation");
                return;
            }
        }

        // Commit CHANGELOG.md first if there are uncommitted changes
        exec('git status --porcelain CHANGELOG.md', $status);
        if (!empty($status)) {
            exec('git add CHANGELOG.md');
            exec("git commit -m 'chore: initialize CHANGELOG for {$tagName}'");
        }

        // Create annotated tag
        exec("git tag -a '{$tagName}' -m '{$message}'");
    }
}

