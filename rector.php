<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;
use Rector\Naming\Rector\Class_\RenamePropertyToMatchTypeRector;
use Rector\Set\ValueObject\LevelSetList;
use Rector\Set\ValueObject\SetList;

return static function (RectorConfig $rectorConfig): void {
    $rectorConfig->paths([__DIR__.'/src', __DIR__.'/tests', __DIR__.'/utils']);
    $rectorConfig->importNames(importNames: true);
    $rectorConfig->parallel();

    $rectorConfig->skip([
        RenamePropertyToMatchTypeRector::class => [__DIR__.'/tests/ORM/'],
    ]);

    $rectorConfig->sets([
        SetList::DEAD_CODE,
        SetList::CODE_QUALITY,
        SetList::CODING_STYLE,
        SetList::NAMING,
        SetList::EARLY_RETURN,
        SetList::PHP_83,
        LevelSetList::UP_TO_PHP_83,
    ]);
};
