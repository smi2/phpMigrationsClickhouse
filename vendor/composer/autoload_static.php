<?php

// autoload_static.php @generated by Composer

namespace Composer\Autoload;

class ComposerStaticInitbf09b5fef2e4f4e8ea2e9a5c04640d55
{
    public static $files = array (
        'a4ecaeafb8cfb009ad0e052c90355e98' => __DIR__ . '/..' . '/beberlei/assert/lib/Assert/functions.php',
    );

    public static $prefixLengthsPsr4 = array (
        'P' => 
        array (
            'PhpSchool\\CliMenu\\' => 18,
        ),
    );

    public static $prefixDirsPsr4 = array (
        'PhpSchool\\CliMenu\\' => 
        array (
            0 => __DIR__ . '/..' . '/php-school/cli-menu/src',
        ),
    );

    public static $fallbackDirsPsr4 = array (
        0 => __DIR__ . '/..' . '/coyl/git/src',
    );

    public static $prefixesPsr0 = array (
        'A' => 
        array (
            'Assert' => 
            array (
                0 => __DIR__ . '/..' . '/beberlei/assert/lib',
            ),
        ),
    );

    public static function getInitializer(ClassLoader $loader)
    {
        return \Closure::bind(function () use ($loader) {
            $loader->prefixLengthsPsr4 = ComposerStaticInitbf09b5fef2e4f4e8ea2e9a5c04640d55::$prefixLengthsPsr4;
            $loader->prefixDirsPsr4 = ComposerStaticInitbf09b5fef2e4f4e8ea2e9a5c04640d55::$prefixDirsPsr4;
            $loader->fallbackDirsPsr4 = ComposerStaticInitbf09b5fef2e4f4e8ea2e9a5c04640d55::$fallbackDirsPsr4;
            $loader->prefixesPsr0 = ComposerStaticInitbf09b5fef2e4f4e8ea2e9a5c04640d55::$prefixesPsr0;

        }, null, ClassLoader::class);
    }
}
