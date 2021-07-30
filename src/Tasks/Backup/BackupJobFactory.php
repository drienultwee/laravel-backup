<?php

namespace Spatie\Backup\Tasks\Backup;

use Illuminate\Support\Collection;
use Illuminate\Support\Arr;
use Spatie\Backup\BackupDestination\BackupDestinationFactory;

class BackupJobFactory
{
    public static function createFromArray(array $config): BackupJob
    {
        return (new BackupJob())
            ->setFileSelection(static::createFileSelection($config['backup']['source']['files']))
            ->setDbDumpers(static::createDbDumpers($config['backup']['source']['databases']))
            ->setBackupDestinations(BackupDestinationFactory::createFromArray($config['backup']));
    }

    protected static function createFileSelection(array $sourceFiles): FileSelection
    {
        return FileSelection::create($sourceFiles['include'])
            ->excludeFilesFrom($sourceFiles['exclude'])
            ->filesModifiedSecondsAgo(isset($sourceFiles['modified_seconds_ago']) ? $sourceFiles['modified_seconds_ago'] : null)
            ->shouldFollowLinks(isset($sourceFiles['follow_links']) && $sourceFiles['follow_links'])
            ->shouldIgnoreUnreadableDirs(Arr::get($sourceFiles, 'ignore_unreadable_directories', false));
    }

    protected static function createDbDumpers(array $dbConnectionNames): Collection
    {
        return collect($dbConnectionNames)->mapWithKeys(function (string $dbConnectionName) {
            return [$dbConnectionName=>DbDumperFactory::createFromConnection($dbConnectionName)];
        });
    }
}
