# Built-in CRUD UI (optional, default off)

The bundle ships optional `RegistryController` and `SystemController` providing a
minimal CRUD interface for registry/system keys. It is **disabled by default**:
the controllers are not registered as services unless you enable them, so even if
their routes are imported they cannot be instantiated.

## Enabling

```yaml
# config/packages/registry.yaml
registry:
    ui:
        enabled: true
        base_template: base.html.twig      # layout the bundle templates extend
        role: ROLE_REGISTRY_ADMIN          # role required to access the UI
```

Requires `symfony/form`, `symfony/twig-bundle` and `symfony/security-csrf`.

## Importing the routes

Import the controller routes **only when the UI is enabled**:

```yaml
# config/routes/registry.yaml
registry_bundle:
    resource: '@RegistryBundle/Controller/'
    type: attribute
```

Routes exposed: `registry_index`, `registry_new`, `registry_edit`,
`registry_delete`, and the `system_*` equivalents.

## Security model

- **Authorization.** Every action calls `denyAccessUnlessGranted()` with the
  configured `ui.role`. Configure your security so that role is only granted to
  trusted administrators.
- **Delete is POST + CSRF only.** The `*_delete` routes accept `POST` exclusively
  and validate a CSRF token (`registry_delete` / `system_delete`). The list views
  render a small POST form per row instead of a delete link.
- **No raw entity deserialization.** `edit`/`delete` identify a key through
  discrete, validated parameters (`user_id`, `key`, `name`, `type`) rather than a
  client-supplied serialized entity. Unknown/invalid references yield 404.

## Customizing the layout

The templates extend `ui.base_template`. Provide a layout that defines the
`title` and `body` blocks, e.g.:

```twig
{# templates/base.html.twig #}
<!DOCTYPE html>
<html>
    <head><title>{% block title %}{% endblock %}</title></head>
    <body>{% block body %}{% endblock %}</body>
</html>
```
