CHANGE LOG
==========

V 6.3.0
-------

- Requires PHP 8.1
- Updated for Symfony 6.3 Branch

V 6.0.0
-------

- Update for PHP 8.* compatibility
- Update for Symfony 5.* compatibility
- Test-Release for Symfony 6.x
- Not ready for production

V 4.1.3
-------

- Updated TreeBuilder to support Symfony 5.0

V 4.1.0
-------

- Simplified registry behavior
- Removed factory
- Removed engine switching
- Use explicit constructors for different engine types

V 4.0.0
-------

- Release for Symfony 4.x
- Introduces some BC, no longer switching of engines

V 2.1.0
-------

- Release for Symfony 3.x
- Introduces some BC for registry configuration methods
  - registry.switchEngine() is now registry.switchEngineType()
  - registry.isMode() is now registry.isEngineType()


V 2.0.5
-------

- Stable release after rewrite of registry-bundle as new registry2-bundle

V 1.x
-----

- Releases for Symfony 2.x
