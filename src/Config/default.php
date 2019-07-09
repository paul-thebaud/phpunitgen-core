<?php

return [
    /*
     |--------------------------------------------------------------------------
     | Automatic Test Generation.
     |
     | Tells if the generator should create advanced tests skeletons and
     | class instantiation.
     |--------------------------------------------------------------------------
     */
    'automaticTests'    => true,

    /*
     |--------------------------------------------------------------------------
     | Mock Library to use.
     |
     | Tells to the generator to prefer using one of the following library when
     | mock creation is required (instantiation, function arguments).
     |  - "mockery" (see https://github.com/mockery/mockery)
     |  - "phpunit" (see https://github.com/sebastianbergmann/phpunit)
     |--------------------------------------------------------------------------
     */
    'mockWith'          => 'mockery',

    /*
     |--------------------------------------------------------------------------
     | Test Generator to use.
     |
     | Tells which generator you want to use.
     |  - "basic" will generate classic PHP class tests (one tests per method)
     |    with automatic getter/setter tests.
     |  - "laravel.policy" will generate tests for Laravel Policy class
     |    (see https://laravel.com/docs/5.8/authorization#creating-policies).
     |--------------------------------------------------------------------------
     */
    'generateWith'      => 'basic',

    /*
     |--------------------------------------------------------------------------
     | Base Namespace of source code.
     |
     | This string will be removed from the test class namespace.
     |--------------------------------------------------------------------------
     */
    'baseNamespace'     => '',

    /*
     |--------------------------------------------------------------------------
     | Base Namespace of tests.
     |
     | This string will be prepend to the test class namespace.
     |--------------------------------------------------------------------------
     */
    'baseTestNamespace' => 'Tests\\',

    /*
     |--------------------------------------------------------------------------
     | Excluded methods.
     |
     | Those methods will not have tests or skeleton generation. This must be an
     | array of RegExp compatible with "preg_match", but without the opening and
     | closing "/", as they will be added automatically.
     |--------------------------------------------------------------------------
     */
    'excludedMethods'   => [
        '__construct',
        '__destruct',
    ],

    /*
     |--------------------------------------------------------------------------
     | Merged PHP documentation tags.
     |
     | Those tags will be retrieved from tested class documentation, and appends
     | to the test class documentation.
     |--------------------------------------------------------------------------
     */
    'mergedPhpDoc'      => [
        'author',
        'copyright',
        'license',
        'version',
    ],

    /*
     |--------------------------------------------------------------------------
     | PHP documentation lines.
     |
     | Those complete documentation line (such as "@author John Doe") will be
     | added to the test class documentation.
     |--------------------------------------------------------------------------
     */
    'phpDoc'            => [],

    /*
     |--------------------------------------------------------------------------
     | Options.
     |
     | This property is for generator's specific configurations. It might
     | contains any other useful information for test generation.
     |--------------------------------------------------------------------------
     */
    'options'           => [],
];
