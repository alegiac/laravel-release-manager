<?php

namespace Alegiac\ReleaseManager\Services;

use Illuminate\Support\Facades\Process;

/**
 * Git Manager
 * 
 * Handles git repository initialization, remote configuration, and branch setup.
 * 
 * @package Alegiac\ReleaseManager\Services
 */
class GitManager
{
    /**
     * Check if directory is a git repository
     *
     * @return bool
     */
    public function isGitRepository(): bool
    {
        return is_dir(base_path('.git'));
    }

    /**
     * Initialize git repository
     *
     * @return bool
     */
    public function initRepository(): bool
    {
        if ($this->isGitRepository()) {
            return true;
        }

        exec('cd ' . base_path() . ' && git init', $output, $returnCode);
        
        return $returnCode === 0;
    }

    /**
     * Get current remote URL
     *
     * @param string $remote
     * @return string|null
     */
    public function getRemoteUrl(string $remote = 'origin'): ?string
    {
        exec("cd " . base_path() . " && git remote get-url {$remote} 2>/dev/null", $output, $returnCode);
        
        return $returnCode === 0 && !empty($output) ? $output[0] : null;
    }

    /**
     * Add remote to repository
     *
     * @param string $url
     * @param string $remote
     * @return bool
     */
    public function addRemote(string $url, string $remote = 'origin'): bool
    {
        // Check if remote already exists
        $existingUrl = $this->getRemoteUrl($remote);
        
        if ($existingUrl) {
            // Update existing remote
            exec("cd " . base_path() . " && git remote set-url {$remote} {$url}", $output, $returnCode);
        } else {
            // Add new remote
            exec("cd " . base_path() . " && git remote add {$remote} {$url}", $output, $returnCode);
        }
        
        return $returnCode === 0;
    }

    /**
     * Get current branch
     *
     * @return string|null
     */
    public function getCurrentBranch(): ?string
    {
        exec('cd ' . base_path() . ' && git rev-parse --abbrev-ref HEAD 2>/dev/null', $output, $returnCode);
        
        return $returnCode === 0 && !empty($output) ? $output[0] : null;
    }

    /**
     * Get all branches
     *
     * @return array
     */
    public function getBranches(): array
    {
        exec('cd ' . base_path() . ' && git branch --format="%(refname:short)" 2>/dev/null', $output);
        
        return $output ?? [];
    }

    /**
     * Create and checkout branch
     *
     * @param string $branch
     * @param bool $checkout
     * @return bool
     */
    public function createBranch(string $branch, bool $checkout = true): bool
    {
        $branches = $this->getBranches();
        
        if (in_array($branch, $branches)) {
            if ($checkout) {
                return $this->checkoutBranch($branch);
            }
            return true;
        }

        $command = $checkout 
            ? "cd " . base_path() . " && git checkout -b {$branch}"
            : "cd " . base_path() . " && git branch {$branch}";
            
        exec($command, $output, $returnCode);
        
        return $returnCode === 0;
    }

    /**
     * Checkout branch
     *
     * @param string $branch
     * @return bool
     */
    public function checkoutBranch(string $branch): bool
    {
        exec("cd " . base_path() . " && git checkout {$branch}", $output, $returnCode);
        
        return $returnCode === 0;
    }

    /**
     * Setup standard branches (main, develop, stage)
     *
     * @return array
     */
    public function setupStandardBranches(): array
    {
        $results = [];
        $currentBranch = $this->getCurrentBranch();
        
        // Ensure we have at least one commit
        if (!$this->hasCommits()) {
            // Create initial commit if needed
            exec('cd ' . base_path() . ' && git commit --allow-empty -m "chore: initial commit"');
        }

        // Create main branch (if not exists)
        if (!$currentBranch || $currentBranch === 'master') {
            exec('cd ' . base_path() . ' && git checkout -b main 2>/dev/null');
            $results['main'] = true;
        } elseif ($currentBranch !== 'main') {
            exec('cd ' . base_path() . ' && git branch main 2>/dev/null');
            $results['main'] = true;
        }

        // Create develop branch
        $developCreated = $this->createBranch('develop', false);
        $results['develop'] = $developCreated;

        // Create stage branch
        $stageCreated = $this->createBranch('stage', false);
        $results['stage'] = $stageCreated;

        // Return to original branch or main
        if ($currentBranch && $currentBranch !== 'master') {
            $this->checkoutBranch($currentBranch);
        } else {
            $this->checkoutBranch('main');
        }

        return $results;
    }

    /**
     * Check if repository has commits
     *
     * @return bool
     */
    public function hasCommits(): bool
    {
        exec('cd ' . base_path() . ' && git rev-parse HEAD 2>/dev/null', $output, $returnCode);
        
        return $returnCode === 0;
    }

    /**
     * Create repository on GitHub (requires GitHub CLI)
     *
     * @param string $name
     * @param bool $private
     * @return array{success: bool, url: string|null, message: string}
     */
    public function createGitHubRepository(string $name, bool $private = false): array
    {
        // Check if GitHub CLI is installed
        exec('which gh', $output, $returnCode);
        
        if ($returnCode !== 0) {
            return [
                'success' => false,
                'url' => null,
                'message' => 'GitHub CLI (gh) not installed. Install from https://cli.github.com/'
            ];
        }

        // Create repository
        $visibility = $private ? '--private' : '--public';
        exec("gh repo create {$name} {$visibility} --source=. --remote=origin 2>&1", $output, $returnCode);
        
        if ($returnCode !== 0) {
            return [
                'success' => false,
                'url' => null,
                'message' => implode("\n", $output)
            ];
        }

        // Get repository URL
        $url = $this->getRemoteUrl('origin');
        
        return [
            'success' => true,
            'url' => $url,
            'message' => 'Repository created successfully'
        ];
    }

    /**
     * Create repository on Bitbucket (requires Bitbucket API)
     *
     * @param string $workspace
     * @param string $name
     * @param bool $private
     * @return array{success: bool, url: string|null, message: string}
     */
    public function createBitbucketRepository(string $workspace, string $name, bool $private = true): array
    {
        // This would require Bitbucket API credentials
        // For now, return manual instructions
        $isPrivate = $private ? 'private' : 'public';
        $sshUrl = "git@bitbucket.org:{$workspace}/{$name}.git";
        
        return [
            'success' => false,
            'url' => $sshUrl,
            'message' => "Please create the repository manually on Bitbucket:\n" .
                        "1. Go to https://bitbucket.org/{$workspace}/repositories\n" .
                        "2. Create new repository: {$name}\n" .
                        "3. Set visibility: {$isPrivate}\n" .
                        "4. Then run: git remote add origin {$sshUrl}"
        ];
    }

    /**
     * Push branches to remote
     *
     * @param array $branches
     * @param string $remote
     * @return array
     */
    public function pushBranches(array $branches, string $remote = 'origin'): array
    {
        $results = [];
        
        foreach ($branches as $branch) {
            exec("cd " . base_path() . " && git push -u {$remote} {$branch} 2>&1", $output, $returnCode);
            $results[$branch] = $returnCode === 0;
        }
        
        return $results;
    }

    /**
     * Push tags to remote
     *
     * @param string $remote
     * @return bool
     */
    public function pushTags(string $remote = 'origin'): bool
    {
        exec("cd " . base_path() . " && git push {$remote} --tags", $output, $returnCode);
        
        return $returnCode === 0;
    }
}

