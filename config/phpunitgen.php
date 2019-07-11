<?php

use PhpUnitGen\Core\Contracts\Generators\Factories\ImportFactory as ImportFactoryContract;
use PhpUnitGen\Core\Contracts\Generators\Factories\ValueFactory as ValueFactoryContract;
use PhpUnitGen\Core\Contracts\Generators\MockGenerator as MockGeneratorContract;
use PhpUnitGen\Core\Contracts\Generators\TestGenerator as TestGeneratorContract;
use PhpUnitGen\Core\Contracts\Parsers\CodeParser as CodeParserContract;
use PhpUnitGen\Core\Contracts\Renderers\Renderer as RendererContract;
use PhpUnitGen\Core\Generators\Factories\ImportFactory;
use PhpUnitGen\Core\Generators\Factories\ValueFactory;
use PhpUnitGen\Core\Generators\Mocks\MockeryMockGenerator;
use PhpUnitGen\Core\Generators\Tests\BasicTestGenerator;
use PhpUnitGen\Core\Parsers\CodeParser;
use PhpUnitGen\Core\Renderers\Renderer;

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
     | Contract implementations to use.
     |
     | Tells which implementation you want to use when PhpUnitGen requires a
     | specific contract.
     |--------------------------------------------------------------------------
     */
    'implementations'   => [
        CodeParserContract::class    => CodeParser::class,
        ImportFactoryContract::class => ImportFactory::class,
        MockGeneratorContract::class => MockeryMockGenerator::class,
        RendererContract::class      => Renderer::class,
        TestGeneratorContract::class => BasicTestGenerator::class,
        ValueFactoryContract::class  => ValueFactory::class,
    ],

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
     | Test Case.
     |
     | The absolute class name to TestCase.
     |--------------------------------------------------------------------------
     */
    'testCase'          => 'PHPUnit\\Framework\\TestCase',

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
    'options'           => [
        /*
         |----------------------------------------------------------------------
         | Laravel Options.
         |
         | Those options are used by Laravel Test Generators.
         |  - "user" is the class of User Eloquent model, since it will be used
         |    in many tests.
         |----------------------------------------------------------------------
         */
        // 'laravel.user' => 'App\\User',
    ],
];
