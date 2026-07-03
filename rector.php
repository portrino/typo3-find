<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;
use Rector\Set\ValueObject\SetList;
use Ssch\TYPO3Rector\Set\Typo3SetList;

return static function (RectorConfig $rectorConfig): void {
    $rectorConfig->paths([
        __DIR__ . '/Classes',
        __DIR__ . '/Tests',
        __DIR__ . '/Configuration',
        __DIR__ . '/Resources',
        __DIR__ . '/*.php',
    ]);

    $rectorConfig->skip([
        __DIR__ . '/.Build/vendor',
        __DIR__ . '/var',
        __DIR__ . '/*.cache',
    ]);

    // Define what rule sets will be applied
    $rectorConfig->sets([
        SetList::CODING_STYLE,
        SetList::CODE_QUALITY,
        SetList::PHP_84,
        Typo3SetList::TYPO3_13,
    ]);
};
