<?php

declare(strict_types=1);

// Copied from vendor/nunomaduro/phpinsights/stubs/config.php and heavily
// modified.

// keep-sorted start
use NunoMaduro\PhpInsights\Domain\Insights\CyclomaticComplexityIsHigh;
use NunoMaduro\PhpInsights\Domain\Insights\ForbiddenDefineFunctions;
use NunoMaduro\PhpInsights\Domain\Insights\ForbiddenGlobals;
use PHP_CodeSniffer\Standards\Generic\Sniffs\PHP\CharacterBeforePHPOpeningTagSniff;
use SlevomatCodingStandard\Sniffs\Classes\ForbiddenPublicPropertySniff;
use SlevomatCodingStandard\Sniffs\Functions\FunctionLengthSniff;
// keep-sorted end

return [

    'preset' => 'wordpress',

    'remove' => [
        CharacterBeforePHPOpeningTagSniff::class,
        ForbiddenDefineFunctions::class,
        ForbiddenGlobals::class,
        ForbiddenPublicPropertySniff::class,
    ],

    'config' => [
        CyclomaticComplexityIsHigh::class => [
            'maxComplexity' => 10,
        ],
        FunctionLengthSniff::class => [
            'maxLinesLength' => 50,
        ],
    ],
];
