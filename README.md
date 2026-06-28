registry2-bundle
================

A key-value data store for Symfony, persisted by the Doctrine entity manager
(default) or, optionally, a Redis store. The Redis engine accepts any redis
client (native `\Redis`, Predis, a symfony/cache adapter, or
[SncRedisBundle](https://github.com/snc/SncRedisBundle)) and has no hard
dependency on a specific one.

[![Latest Stable Version](https://poser.pugx.org/jonasarts/registry2-bundle/v)](https://packagist.org/packages/jonasarts/registry2-bundle)
[![Total Downloads](https://poser.pugx.org/jonasarts/registry2-bundle/downloads)](https://packagist.org/packages/jonasarts/registry2-bundle)
[![License](https://poser.pugx.org/jonasarts/registry2-bundle/license)](https://packagist.org/packages/jonasarts/registry2-bundle)
[![CI](https://github.com/jonasarts/registry2-bundle/actions/workflows/ci.yml/badge.svg)](https://github.com/jonasarts/registry2-bundle/actions/workflows/ci.yml)

Requires PHP 8.4 and Symfony `^7.0 || ^8.0`.

Installation
------------

All installation instructions are in the [documentation](docs/index.md).

Documentation
-------------

* [Documentation index](docs/index.md)
* [Upgrade from 7.x to 8.0](docs/UPGRADE-8.0.md)
* [Change log](CHANGELOG.md)

License
-------

This bundle is released under the MIT license. See [LICENSE](LICENSE).
