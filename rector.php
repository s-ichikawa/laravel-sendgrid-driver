<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;
use Rector\Php80\Rector\Class_\ClassPropertyAssignToConstructorPromotionRector;
use Rector\TypeDeclaration\Rector\ClassMethod\AddVoidReturnTypeWhereNoReturnRector;
use Rector\TypeDeclaration\Rector\ClassMethod\ReturnUnionTypeRector;
use Rector\TypeDeclaration\Rector\StmtsAwareInterface\SafeDeclareStrictTypesRector;

return RectorConfig::configure()
    ->withPaths([
        __DIR__.'/src',
        __DIR__.'/tests',
    ])
    // Laravel 9 still allows PHP 8.0, so stay on PHP 8.0 syntax until 5.0
    ->withPhpSets(php80: true)
    ->withPreparedSets(
        deadCode: true,
        codeQuality: true,
        typeDeclarations: true,
        privatization: true,
        earlyReturn: true,
    )
    ->withSkip([
        // The following would be BC breaks for a 4.x patch release and are
        // deferred to 5.0:
        // - strict_types changes coercion behaviour for callers
        SafeDeclareStrictTypesRector::class => [__DIR__.'/src'],
        // - native return types on public methods break subclasses overriding them
        AddVoidReturnTypeWhereNoReturnRector::class => [__DIR__.'/src'],
        ReturnUnionTypeRector::class => [__DIR__.'/src'],
        // - promotion renames the $api_key constructor parameter
        ClassPropertyAssignToConstructorPromotionRector::class => [__DIR__.'/src'],
    ]);
