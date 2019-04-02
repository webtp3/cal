<?php

/*
 * This file is part of the web-tp3/cal.
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

/**
 * This file is defined in UnitTests.xml and called by phpunit
 * before instantiating the test suites, it must also be included
 * with phpunit parameter --bootstrap if executing single test case classes.
 *
 * Run whole core unit test suite, example:
 * - cd /var/www/t3master/foo  # Document root of TYPO3 CMS instance (location of index.php)
 * - typo3/../bin/phpunit -c components/testing_framework/core/Build/UnitTests.xml
 *
 * Run single test case, example:
 * - cd /var/www/t3master/foo  # Document root of TYPO3 CMS instance (location of index.php)
 * - typo3/../bin/phpunit -c components/testing_framework/core/Build/UnitTests.xml
 *     typo3/sysext/core/Tests/Unit/DataHandling/DataHandlerTest.php
 */
call_user_func(function () {
    $testbase = new \TYPO3\TestingFramework\Core\Testbase();
    $testbase->enableDisplayErrors();
    $testbase->defineBaseConstants();
    $testbase->defineSitePath();
    $testbase->defineTypo3ModeBe();
    $testbase->setTypo3TestingContext();
    $testbase->definePackagesPath();
    $testbase->createDirectory(PATH_site . 'typo3conf/ext');
    $testbase->createDirectory(PATH_site . 'typo3temp/assets');
    $testbase->createDirectory(PATH_site . 'typo3temp/var/tests');
    $testbase->createDirectory(PATH_site . 'typo3temp/var/transient');
    $testbase->createDirectory(PATH_site . 'uploads');

    // disable TYPO3_DLOG
    define('TYPO3_DLOG', false);

    // Retrieve an instance of class loader and inject to core bootstrap
    // selftest
    if (file_exists($classLoaderFilepath = dirname(PATH_site) . '/Build/vendor/autoload.php')) {
        // Console is root package, thus vendor folder is .Build/vendor
        $classLoader = require $classLoaderFilepath;
    } elseif (file_exists($classLoaderFilepath = dirname(PATH_site) . '/.Build/vendor/autoload.php')) {
        // Console is root package, thus vendor folder is .Build/vendor
        $classLoader = require $classLoaderFilepath;
    } elseif (file_exists($vendorAutoLoadFile = dirname(dirname(dirname(__DIR__))) . '/autoload.php')) {
        // Console is a dependency, thus located in vendor/helhum/typo3-console
        $classLoader = require $vendorAutoLoadFile;
    } elseif (file_exists($typo3AutoLoadFile = $_SERVER['PWD'] . '/.Build/vendor/autoload.php')) {
        // Console is extension CAG
        $classLoader = require $typo3AutoLoadFile;
    } else {
        echo 'Could not find autoload.php file. TYPO3 Console needs to be installed with composer' . PHP_EOL;
        exit(1);
    }
    if (!file_exists($classLoaderFilepath)) {
        die('ClassLoader can\'t be loaded. Please check your path or set an environment variable \'TYPO3_PATH_ROOT\' to your root path.');
    }
    // $classLoader = require $classLoaderFilepath;
    \TYPO3\CMS\Core\Core\Bootstrap::getInstance()
        ->initializeClassLoader($classLoader)
        ->setRequestType(TYPO3_REQUESTTYPE_BE | TYPO3_REQUESTTYPE_CLI)
        ->baseSetup();

    // Initialize default TYPO3_CONF_VARS
    $configurationManager = new \TYPO3\CMS\Core\Configuration\ConfigurationManager();
    $GLOBALS['TYPO3_CONF_VARS'] = $configurationManager->getDefaultConfiguration();
    // Avoid failing tests that rely on HTTP_HOST retrieval
    $GLOBALS['TYPO3_CONF_VARS']['SYS']['trustedHostsPattern'] = '.*';

    \TYPO3\CMS\Core\Core\Bootstrap::getInstance()
        ->disableCoreCache()
        ->initializeCachingFramework()
        // Set all packages to active
        ->initializePackageManagement(\TYPO3\CMS\Core\Package\UnitTestPackageManager::class);

    if (!\TYPO3\CMS\Core\Core\Bootstrap::usesComposerClassLoading()) {
        // Dump autoload info if in non composer mode
        \TYPO3\CMS\Core\Core\ClassLoadingInformation::dumpClassLoadingInformation();
        \TYPO3\CMS\Core\Core\ClassLoadingInformation::registerClassLoadingInformation();
    }
});
