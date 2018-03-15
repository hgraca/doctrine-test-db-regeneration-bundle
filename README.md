# Hgraca \ DoctrineTestDbRegenerationBundle
[![Author][Author]](https://www.herbertograca.com)
[![Software License][License]](LICENSE)
[![Latest Version][Version]](https://github.com/hgraca/doctrine-test-db-regeneration-bundle/releases)
[![Total Downloads][Downloads]](https://packagist.org/packages/hgraca/doctrine-test-db-regeneration-bundle)

[![Build Status][Build]](https://scrutinizer-ci.com/g/hgraca/doctrine-test-db-regeneration-bundle/build-status/master)
[![Scrutinizer Code Quality][Score]](https://scrutinizer-ci.com/g/hgraca/doctrine-test-db-regeneration-bundle/?branch=master)
[![Code Intelligence Status][CodeInt]](https://scrutinizer-ci.com/code-intelligence)
[![CodeCov][CodeCov]](https://codecov.io/gh/hgraca/doctrine-test-db-regeneration-bundle)

This is a Symfony 2/3/4 bundle, inspired on some code [@lcobucci] wrote back in 2017, to generate the test DB 
 (if needed) for every test suite run or test method run.

When we have Integration tests that need to use a DB, the best practice is to have the tests use a SQLite DB for
 those tests. However, keeping the SQLite DB file in our repo is not a good practice, and using the same file for all
 tests neither, because tests should run in isolation from each other.
 
This bundle provides the functionality to generate the test DB SQLite file, and regenerate it for every test that needs
 a DB. 

By default, at the beginning of a test run it will create a test database, based on the Doctrine fixtures, and
 a serialized fixtures object. 
 
After generating those initial files, it will copy the backup DB SQLite file into the path of the DB SQLite file being
 used by the tests, but only before the tests implementing the tag interface `DatabaseAwareTestInterface` so that we 
 don't regenerate the DB for tests that don't need it.
 
This last functionality, however, can be disabled so that we can use this bundle with another bundle like the
 [DAMA\DoctrineTestBundle], which provides similar functionality which will probably give you better performance.
 In such case, this bundle is only useful for generating the initial test DB.

## Installation & Setup

1. install via composer

    ```bash
    composer require --dev hgraca/doctrine-test-db-regeneration-bundle
    ```

2. Enable the bundle for your test environment in your `AppKernel.php`, ie:

    ```php
    if (in_array($env, ['dev', 'test'])) {
        ...
        if ($env === 'test') {
            $bundles[] = new Hgraca\DoctrineTestDbRegenerationBundle\HgracaDoctrineTestDbRegenerationBundle();
        }
    }
    ```

3. The bundle exposes a configuration that looks like this by default:
    
    ```yaml
    hgraca_doctrine_test_db_regeneration:
        doctrine_service_id: 'doctrine'
        fixtures_loader_service_id: 'doctrine.fixtures.loader'
        test_db_bkp_dir: '%kernel.cache_dir%/test.bkp.db'
        # The list of extra services to add to the container, 
        # in case you want to reuse this already built container.
        extra_service_list: [] 
    ```
4. Add the PHPUnit test listener in your xml config (e.g. `app/phpunit.xml`) 

    ```xml
    <phpunit>
        ...
    <listeners>
        <!-- At the beginning of every test run, it will generate the test DB and create a backup of it and 
             a backup of the fixtures references. At the beginning of every test it will recover the test DB backup. -->
        <listener class="\Hgraca\DoctrineTestDbRegenerationBundle\EventSubscriber\DbRegenerationPHPUnitEventSubscriber" />
    </listeners>
    </phpunit>
    ```
    
    There are a few options you can use to tweak how it works. Below you can see the options with their default values:
    
    ```xml
    <phpunit>
        ...
    <listeners>
        <listener class="\Hgraca\DoctrineTestDbRegenerationBundle\EventSubscriber\DbRegenerationPHPUnitEventSubscriber">
            <arguments>
                <integer>1</integer> <!-- $shouldRemoveDbAfterEveryTest -->
                <integer>1</integer> <!-- $shouldRegenerateOnEveryTest -->
                <integer>0</integer> <!-- $shouldReuseExistingDbBkp -->
            </arguments>
        </listener>
    </listeners>
    </phpunit>
    ```
    
    I strongly advise using [DAMA\DoctrineTestBundle] as well, as in a platform with 1596 integration tests, 
    it reduced the test execution time from 2m to 46s.
    
    If you are using [DAMA\DoctrineTestBundle] you will want to set it up like this: 
    
    ```xml
    <phpunit>
        ...
    <listeners>
        <!-- Since we are using DAMA\DoctrineTestBundle we don't need to recover the test DB at 
             every test run, so we turn it off. However, this listener must go first, so it 
             creates the test DB before DAMA\DoctrineTestBundle tries to use it. -->
        <listener class="\Hgraca\DoctrineTestDbRegenerationBundle\EventSubscriber\DbRegenerationPHPUnitEventSubscriber">
            <arguments>
                <integer>0</integer> <!-- $shouldRemoveDbAfterEveryTest -->
                <integer>0</integer> <!-- $shouldRegenerateOnEveryTest -->
            </arguments>
        </listener>
        <!-- it begins a database transaction before every testcase and rolls it back after
             the test finished, so tests can manipulate the database without affecting other tests -->
        <listener class="\DAMA\DoctrineTestBundle\PHPUnit\PHPUnitListener" />
    </listeners>
    </phpunit>
    ```
5. Make your tests, that need DB access, implement the tag interface `DatabaseAwareTestInterface`, so it only
 recovers the DB for those tests.
6. Done! From now on the test database will be generated (if needed) at the beginning of running the tests,
 and at the beginning of every test tagged as `DatabaseAwareTestInterface`, if you did not disable this feature.

## Available commands

Run the test suites:
```bash
make test
```
Create th test coverage report:
```bash
make coverage
```
Fix the code standards:
```bash
make cs-fix
```
Manage the project dependencies:
```bash
make dep-install
make dep-update
```
Build the docker container used for testing:
```bash
make build-container-tst
```

[Author]: http://img.shields.io/badge/author-@hgraca-blue.svg?style=flat-square
[License]: https://img.shields.io/badge/license-MIT-blue.svg?style=flat-square
[Version]: https://img.shields.io/github/release/hgraca/doctrine-test-db-regeneration-bundle.svg?style=flat-square
[Downloads]: https://img.shields.io/packagist/dt/hgraca/doctrine-test-db-regeneration-bundle.svg?style=flat-square

[Build]: https://scrutinizer-ci.com/g/hgraca/doctrine-test-db-regeneration-bundle/badges/build.png?b=master
[Score]: https://scrutinizer-ci.com/g/hgraca/doctrine-test-db-regeneration-bundle/badges/quality-score.png?b=master
[CodeInt]: https://scrutinizer-ci.com/g/hgraca/doctrine-test-db-regeneration-bundle/badges/code-intelligence.svg?b=master
[CodeCov]: https://codecov.io/gh/hgraca/doctrine-test-db-regeneration-bundle/branch/master/graph/badge.svg

[DAMA\DoctrineTestBundle]: https://github.com/dmaicher/doctrine-test-bundle

[@lcobucci]: https://github.com/lcobucci
