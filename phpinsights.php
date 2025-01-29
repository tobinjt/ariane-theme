<?php

declare(strict_types=1);

// Copied from vendor/nunomaduro/phpinsights/stubs/config.php and heavily
// modified.

// keep-sorted start
use NunoMaduro\PhpInsights\Domain\Insights\CyclomaticComplexityIsHigh;
use NunoMaduro\PhpInsights\Domain\Insights\ForbiddenDefineFunctions;
use NunoMaduro\PhpInsights\Domain\Insights\ForbiddenGlobals;
use PHP_CodeSniffer\Standards\Generic\Sniffs\Files\LineLengthSniff;
use PHP_CodeSniffer\Standards\Generic\Sniffs\PHP\CharacterBeforePHPOpeningTagSniff;
use PHP_CodeSniffer\Standards\PSR1\Sniffs\Classes\ClassDeclarationSniff;
use PHP_CodeSniffer\Standards\Squiz\Sniffs\Classes\ValidClassNameSniff;
use SlevomatCodingStandard\Sniffs\Classes\ForbiddenPublicPropertySniff;
use SlevomatCodingStandard\Sniffs\Functions\FunctionLengthSniff;
// keep-sorted end

return [

    'preset' => 'wordpress',

    'remove' => [
        // keep-sorted start
        // Requires classes to be inside a namespace, which doesn't work for the
        // fake WordPress classes.
        ClassDeclarationSniff::class,
        // Complains about top-level functions, which I need some of for
        // WordPress fakes.
        ForbiddenDefineFunctions::class,
        // I've reduced globals as much as possible, but I can't see a way to
        // remove the remaining ones.
        ForbiddenGlobals::class,
        // keep-sorted end
    ],

    'config' => [
        // keep-sorted start block=yes
        CharacterBeforePHPOpeningTagSniff::class => [
            'exclude' => [
                // This reports every time you interleave code and HTML.
                // keep-sorted start
                '404.php',
                'footer.php',
                'header.php',
                'index.php',
                'nav.php',
                'page.php',
                // keep-sorted end
            ],
        ],
        CyclomaticComplexityIsHigh::class => [
            'maxComplexity' => 10,
        ],
        ForbiddenPublicPropertySniff::class => [
            'exclude' => [
                // The fake Wordpress classes used in testing must use public
                // properties rather than getters.
                'src/FakeWP_Post.php',
                'src/FakeWP_Query.php',
            ],
        ],
        FunctionLengthSniff::class => [
            'maxLinesLength' => 65,
        ],
        LineLengthSniff::class => [
            'exclude' => [
                // The lines in phpinsights.php are too long.
                'phpinsights.php',
            ],

        ],
        ValidClassNameSniff::class => [
            'exclude' => [
                // The fake Wordpress classes used in testing must use the same names as
                // the real classes.
                // keep-sorted start
                'WP_Post',
                'WP_Query',
                // keep-sorted end
            ],
        ],
        // keep-sorted end
    ],
];
