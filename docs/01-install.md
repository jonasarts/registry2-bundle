Setting up the bundle
=====================

## Install the bundle

Execute this console command in your project:

``` bash
composer require jonasarts/registry2-bundle
```

The Doctrine engine is the default and works out of the box (it requires
`doctrine/doctrine-bundle`). The Redis engine is optional — see
[configuration](02-configuration.md).

## Enable the bundle

Symfony Flex enables the bundle for you in `config/bundles.php`.

You can now type-hint
`jonasarts\Bundle\RegistryBundle\Registry\RegistryInterface` in your services
and controllers; it is bound to the configured engine automatically.

```php
use jonasarts\Bundle\RegistryBundle\Registry\RegistryInterface;

final class MyService
{
    public function __construct(private readonly RegistryInterface $registry)
    {
    }
}
```

## Optional CRUD controllers

The bundle ships optional `RegistryController` / `SystemController`. They are
**disabled by default**. To enable and import their (attribute-based) routes,
see [the CRUD UI guide](05-crud-ui.md):

```yaml
# config/routes/registry.yaml  (only when registry.ui.enabled is true)
registry_bundle:
    resource: '@RegistryBundle/Controller/'
    type: attribute
```

This registers the routes `registry_index`, `registry_new`, `registry_edit`,
`registry_delete` and the `system_*` equivalents.

## Configuration options

[Read the bundle configuration options](02-configuration.md)

## Create the default key/name-values

If you wish to use a central place to store all application-defined default
values, create a defaults file and point `registry.globals.default_values` at
it:

```yaml
# config/packages/registry.yaml
registry:
    globals:
        default_values: '%kernel.project_dir%/config/registry_defaults.yaml'
```

The file has a `registry` and a `system` section. Each entry key is the path
`<key><delimiter><name>`, where `<delimiter>` is `registry.globals.delimiter`
(default `:`):

```yaml
# config/registry_defaults.yaml
registry:
    'settings:page_size': 10
    'settings:language': de_DE
system:
    'some:bln_value': true
    'some:int_value': 5
    'some:str_value': a string
    'some:flt_value': 0.5
    'some:dat_value': 2013-10-15
```

This is **optional**. When the file is configured, its values are used as the
last step of the read fallback chain (user key → user-0 key → defaults file)
for **both** engines — the fallback lives in `AbstractRegistry`, not in a
specific engine. To supply a default for a single call instead, use
`registryReadDefault()` / `systemReadDefault()`.

## That's it

Check out the docs for information on how to use the bundle! [Return to the index.](index.md)
