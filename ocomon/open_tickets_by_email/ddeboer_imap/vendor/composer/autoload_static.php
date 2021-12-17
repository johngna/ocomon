<?php

// autoload_static.php @generated by Composer

namespace Composer\Autoload;

class ComposerStaticInit287b2f096c086d72a4250fdc1a3318ac
{
    public static $prefixLengthsPsr4 = array (
        'D' => 
        array (
            'Ddeboer\\Imap\\' => 13,
        ),
    );

    public static $prefixDirsPsr4 = array (
        'Ddeboer\\Imap\\' => 
        array (
            0 => __DIR__ . '/..' . '/ddeboer/imap/src',
        ),
    );

    public static $classMap = array (
        'Composer\\InstalledVersions' => __DIR__ . '/..' . '/composer/InstalledVersions.php',
    );

    public static function getInitializer(ClassLoader $loader)
    {
        return \Closure::bind(function () use ($loader) {
            $loader->prefixLengthsPsr4 = ComposerStaticInit287b2f096c086d72a4250fdc1a3318ac::$prefixLengthsPsr4;
            $loader->prefixDirsPsr4 = ComposerStaticInit287b2f096c086d72a4250fdc1a3318ac::$prefixDirsPsr4;
            $loader->classMap = ComposerStaticInit287b2f096c086d72a4250fdc1a3318ac::$classMap;

        }, null, ClassLoader::class);
    }
}
