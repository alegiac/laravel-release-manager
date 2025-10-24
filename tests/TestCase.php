<?php

namespace Alegiac\ReleaseManager\Tests;

use Orchestra\Testbench\TestCase as BaseTestCase;
use Alegiac\ReleaseManager\ReleaseManagerServiceProvider;

/**
 * Base Test Case for Release Manager Package
 * 
 * @package Alegiac\ReleaseManager\Tests
 */
abstract class TestCase extends BaseTestCase
{
    /**
     * Set up the test environment.
     *
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();
    }

    /**
     * Get package providers.
     *
     * @param \Illuminate\Foundation\Application $app
     * @return array
     */
    protected function getPackageProviders($app): array
    {
        return [
            ReleaseManagerServiceProvider::class,
        ];
    }

    /**
     * Define environment setup.
     *
     * @param \Illuminate\Foundation\Application $app
     * @return void
     */
    protected function defineEnvironment($app): void
    {
        $app['config']->set('release-manager', [
            'default_version' => '0.0.1',
            'changelog_file' => 'CHANGELOG.md',
            'tag_prefix' => 'v',
            'commit_message' => 'chore(release): {version}',
            'commit_types' => [
                'feat' => 'Features',
                'fix' => 'Bug Fixes',
                'docs' => 'Documentation',
            ],
            'versioning' => [
                'breaking_change_bumps_major' => true,
                'feat_bumps_minor' => true,
                'fix_bumps_patch' => true,
            ],
        ]);
    }
}

