<?php

// autoload_static.php @generated by Composer

namespace Composer\Autoload;

class ComposerStaticInitecda141650001ab03862bb1e8481b8d6
{
    public static $prefixLengthsPsr4 = array (
        'T' => 
        array (
            'TrytoMqtt\\' => 10,
        ),
    );

    public static $prefixDirsPsr4 = array (
        'TrytoMqtt\\' => 
        array (
            0 => __DIR__ . '/..' . '/try-to/swoole_mqtt/src',
        ),
    );

    public static function getInitializer(ClassLoader $loader)
    {
        return \Closure::bind(function () use ($loader) {
            $loader->prefixLengthsPsr4 = ComposerStaticInitecda141650001ab03862bb1e8481b8d6::$prefixLengthsPsr4;
            $loader->prefixDirsPsr4 = ComposerStaticInitecda141650001ab03862bb1e8481b8d6::$prefixDirsPsr4;

        }, null, ClassLoader::class);
    }
}
