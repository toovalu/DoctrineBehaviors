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

// <?php

// declare(strict_types=1);

// use Rector\Caching\ValueObject\Storage\FileCacheStorage;
// use Rector\CodeQuality\Rector\Class_\InlineConstructorDefaultToPropertyRector;
// use Rector\CodeQuality\Rector\If_\ExplicitBoolCompareRector;
// use Rector\Config\RectorConfig;
// use Rector\Php70\Rector\StmtsAwareInterface\IfIssetToCoalescingRector;
// use Rector\Php73\Rector\BooleanOr\IsCountableRector;
// use Rector\Php74\Rector\Closure\ClosureToArrowFunctionRector;
// use Rector\Php80\Rector\Class_\ClassPropertyAssignToConstructorPromotionRector;
// use Rector\Php82\Rector\Class_\ReadOnlyClassRector;
// use Rector\PHPUnit\Set\PHPUnitSetList;
// use Rector\Set\ValueObject\LevelSetList;
// use Rector\Set\ValueObject\SetList;
// use Rector\Symfony\CodeQuality\Rector\Class_\EventListenerToEventSubscriberRector;
// use Rector\Symfony\Set\SensiolabsSetList;
// use Rector\Symfony\Set\SymfonySetList;
// use Rector\Symfony\Set\TwigSetList;
// use Rector\Symfony\Symfony62\Rector\ClassMethod\ParamConverterAttributeToMapEntityAttributeRector;
// use Rector\ValueObject\PhpVersion;

// return RectorConfig::configure()
//     ->withSymfonyContainerXml(__DIR__ . '/var/cache/dev/App_KernelDevDebugContainer.xml')
//     ->withSymfonyContainerPhp(__DIR__ . '/tests/symfony-container.php')
//     ->withPaths([
//         __DIR__ . '/config',
//         __DIR__ . '/src',
//         __DIR__ . '/tests',
//         __DIR__ . '/templates',
//     ])
//     ->withCache(
//         // ensure file system caching is used instead of in-memory
//         cacheClass: FileCacheStorage::class,

//         // specify a path that works locally as well as on CI job runners
//         cacheDirectory: '/tmp/rector',
//     )
//     ->withPreparedSets(codeQuality: true, codingStyle: true)
//     ->withAttributesSets(symfony: true, doctrine: true)
//     ->withParallel(timeoutSeconds: 180, maxNumberOfProcess: 20, jobSize: 20)
//     ->withImportNames(importNames: true, removeUnusedImports: true, importShortClasses: false)
//     // register a single rule
//     ->withRules(
//         [
//             InlineConstructorDefaultToPropertyRector::class,
//             ParamConverterAttributeToMapEntityAttributeRector::class
//         ]
//     )
//     ->withSkip([
//         ClassPropertyAssignToConstructorPromotionRector::class,
//         EventListenerToEventSubscriberRector::class,
//         IfIssetToCoalescingRector::class,
//         IsCountableRector::class,
//         ExplicitBoolCompareRector::class => [
//             __DIR__ . '/src/Entity/EmissionFactor/EmissionFactorTranslation.php',
//         ],
//         ClosureToArrowFunctionRector::class => [
//             __DIR__ . '/src/Entity/Trait/QuestionTrait.php',
//         ],
//         ReadOnlyClassRector::class => [
//             __DIR__ . '/src/Api/Type/Definition',
//         ],
//         __DIR__ . '/src/Kernel.php',
//         __DIR__ . '/config/bundles.php',
//     ])
//     ->withPhpVersion(PhpVersion::PHP_83)
//     //    ->withPHPStanConfigs([__DIR__ . '/phpstan.neon.dist'])
//     //     define sets of rules
//     ->withSets([
//         LevelSetList::UP_TO_PHP_83,
//         SetList::CODE_QUALITY,
//         SetList::CODING_STYLE,
//         SetList::EARLY_RETURN,
//         SetList::PHP_83,
//         SymfonySetList::SYMFONY_64,
//         SymfonySetList::SYMFONY_CODE_QUALITY,
//         SymfonySetList::SYMFONY_CONSTRUCTOR_INJECTION,
//         SymfonySetList::ANNOTATIONS_TO_ATTRIBUTES,
//         SymfonySetList::SYMFONY_CONSTRUCTOR_INJECTION,
//         SymfonySetList::ANNOTATIONS_TO_ATTRIBUTES,
//         SensiolabsSetList::ANNOTATIONS_TO_ATTRIBUTES,
//         PHPUnitSetList::PHPUNIT_CODE_QUALITY,
//         TwigSetList::TWIG_240,
//     ]);
