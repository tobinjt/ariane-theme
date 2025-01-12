<?php

declare(strict_types=1);

// Copied from vendor/nunomaduro/phpinsights/stubs/config.php and heavily
// modified.

// keep-sorted start
use NunoMaduro\PhpInsights\Domain\Insights\CyclomaticComplexityIsHigh;
use PHP_CodeSniffer\Standards\Generic\Sniffs\PHP\CharacterBeforePHPOpeningTagSniff;
use SlevomatCodingStandard\Sniffs\Functions\FunctionLengthSniff;
// keep-sorted end

return [

    'preset' => 'wordpress',

    'remove' => [
        CharacterBeforePHPOpeningTagSniff::class,
    ],

    'config' => [
        CyclomaticComplexityIsHigh::class => [
            'maxComplexity' => 10,
        ],
        FunctionLengthSniff::class => [
            'maxLinesLength' => 50,
            /*'maxLinesLengthIgnoreComments' => true,*/
        ],
    ],
];
