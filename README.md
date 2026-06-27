registry2-bundle
================

**This is the new version 2 of registry-bundle. It breaks the API of version 1!**

This bundle provides a key-value data store, persisted by the Doctrine entity
manager (default) or, optionally, a Redis store. The Redis engine accepts any
redis client (native `\Redis`, Predis, a symfony/cache adapter, or
[SncRedisBundle](https://github.com/snc/SncRedisBundle)) and has no hard
dependency on a specific one.

Requires PHP 8.4 and Symfony 8.

[![Latest Stable Version](https://poser.pugx.org/jonasarts/registry2-bundle/v/stable.png)](https://packagist.org/packages/jonasarts/registry2-bundle)

Installation
------------

All the installation instructions are located in the [documentation](https://github.com/jonasarts/registry2-bundle/blob/master/docs/index.md).

License
-------

This bundle is under the MIT license. See the complete license in the bundle.

The most recent version:
[LICENSE](https://github.com/jonasarts/registry2-bundle/blob/master/LICENSE)
