Using the bundle
================

The Registry (aka RegistryManager) is a service to handle all registry related
operations.
This covers writing, reading and deleting values from or to the registry
storage.

Following methods are present on the Registry class:
- registryExists(\<UserID\>, \<KeyString\>, \<NameString\>, \<TypeIdentifier\>)
- registryWrite(\<UserID\>, \<KeyString\>, \<NameString\>, \<TypeIdentifier\>, \<Value\>);
- registryRead(\<UserID\>, \<KeyString\>, \<NameString\>, \<TypeIdentifier\>);
- registryReadDefault(\<UserID\>, \<KeyString\>, \<NameString\>, \<TypeIdentifier\>, \<DefaultValue\>);
- registryDelete(\<UserID\>, \<KeyString\>, \<NameString\>, \<TypeIdentifier\>);
- systemExists(\<KeyString\>, \<NameString\>, \<TypeIdentifier\>)
- systemWrite(\<KeyString\>, \<NameString\>, \<TypeIdentifier\>, \<Value\>);
- systemRead(\<KeyString\>, \<NameString\>, \<TypeIdentifier\>);
- systemReadDefault(\<KeyString\>, \<NameString\>, \<TypeIdentifier\>, \<DefaultValue\>);
- systemDelete(\<KeyString\>, \<NameString\>, \<TypeIdentifier\>);

For lazy programmers, there are shortcuts to the above methods:
- re() -> registryExists()
- rw() -> registryWrite()
- rr() -> registryRead()
- rrd() -> registryReadDefault()
- rd() -> registryDelete()
- se() -> systemExists()
- sw() -> systemWrite()
- sr() -> systemRead()
- srd() -> systemReadDefault()
- sd() -> systemDelete()

\<UserID\> must be an integer type value.  
\<KeyString\> is a string value; best practice is to use them like namespaces.  
\<NameString\> is a string value; use this string like a name.  
\<TypeIdentifier\> is one of the following identifier strings:
* b, bln, boolean
* i, int, integer
* s, str, string
* f, flt, float
* d, dat, date, t, tim, time

\<Value\> can be of any type which \<TypeIdentifier\> can designate.

Retrieve the Registry service like any other symfony service:

```php
    $rm = $this->get('registry');
```

In the php code examples, ``$this`` referes to a controller.


Configuration Methods
=====================

registry.switchEngineType($engine_type, ContainerInterface $container)
----------------------------------------------------------------------

To switch the database engine used by the registry service, call
`switchEngineType` with an engine type of

- 'doctrine'
- 'redis'

and the service container.

To query the current mode, there are following methods present
- registry.getEngineType() -> string
- registry.isEngineType($engine_type) -> boolean


Registry
========

The Registry methods provide a convenient way to store and retrieve values per
user.

Write a user specific key
-------------------------

Following examples write some registry keys
(more exactly: some registry key/name-values)
for the User with ID 1.

```php
    $rm = $this->get('registry');
    $rm->registryWrite(1, 'App/Test', 'TestInteger', 'i', 1);
    $rm->registryWrite(1, 'App/Test', 'TestBoolean', 'b', true);
    $rm->registryWrite(1, 'App/Test', 'TestString', 's', 'test');
    $rm->registryWrite(1, 'App/Test', 'TestFloat', 'f', 1.1);
    $rm->registryWrite(1, 'App/Test', 'TestDate', 'd', time());
```

If a user specific value matches an already present user-0 value,
the key will not be written and an already older present user specific
registry key will be removed.

```php
    $rm = $this->get('registry');
    $rm->registryWrite(0, 'App/Test', 'TestInteger', 'i', 1); // writes a user-0 key (more info further below)
    $rm->registryWrite(1, 'App/Test', 'TestInteger', 'i', 100); // writes a user specific key
    $rm->registryWrite(1, 'App/Test', 'TestInteger', 'i', 1); // instead of writing a user specific key, this removes the before created user specific key (with value 100) to fall back on the user-0 key
```

Write a user-0 key
------------------

User-0 keys can be used to provide customized defaults, which can be different
from the programmatically provided ones. This enables you to deliver very
different defaults for all users in different installations of your application.

The handling of user-0 keys is absolutely the same as the user specific ones.

```php
    $rm = $this->get('registry');
    $rm->registryWrite(0, 'App/Test', 'TestInteger', 'i', 2);
    $rm->registryWrite(0, 'App/Test', 'TestBoolean', 'b', false);
    $rm->registryWrite(0, 'App/Test', 'TestString', 's', 'one test more');
    $rm->registryWrite(0, 'App/Test', 'TestFloat', 'f', 2.5);
    $rm->registryWrite(0, 'App/Test', 'TestDate', 'd', strtotime('2013-10-16'));
```

Declare a programatic default key
---------------------------------

To provide default values, you can either use the api (registryReadDefault())
or use the registry.yml functionality. If no user key and no user-0 key is
present, the declared default will be returned.

A minimal registry.yml looks like this:

```yaml
registry:
    ~
system:
    ~
```

Keys are added as 'paths'. Just combine the key and name together.

```yaml
registry:
    App/RegistryKey/Name: Value
system:
    ~
```

```php
    $rm = $this->get('registry');
    $value = $rm->registryRead(10, 'App/RegistryKey', 'Name', 's');

    // $value will hold 'Value' if no user-specific/user-0 value is stored in the database.
```

Read a key
----------

The following example reads a registry key for user 10. This will either
be an user specific key or an user-0 key (or even a programatic default value,
if registry.yml is in use).

The retrieval hierarchy is:
* user specific key
* user-0 key
* programatic default key

```php
    $rm = $this->get('registry');
    $value = $rm->registryRead(10, 'App/Test', 'TestString', 's');
```

If you like to provide a default on the fly, you can use the
registryReadDefault() method. The last value is the default, which will be
returned if no user specific / user-0 key is found.
The following example reads a registry key for user 11 and defines a default
value of 'I am the default string'.

```php
    $rm = $this->get('registry');
    $value = $rm->registryReadDefault(11, 'App/Test', 'TestString', 's', 'I am the default string');
```

Delete a key
------------

To remove a registry key (user specific or user-0), just call RegistryDelete.

```php
    $rm = $this->get('registry');
    $rm->registryDelete(1, 'App/Test', 'TestString', 's');
```


System
======

The System functions provide a convenient way to store and retrieve values which
are not related to users.

This is commonly used for application settings.

The system methods are 1:1 related to the registry methods, just not needing a
user id field. As there is no user information, there is no need for user-0
functionality. But there still is a programatic default key, either by the
registry.yml file or by systemReadDefault().


Write a key
-----------

```php
    $rm = $this->get('registry');
    $rm->systemWrite('App/Test', 'TestInteger', 'i', 1);
    $rm->systemWrite('App/Test', 'TestBoolean', 'b', true);
    $rm->systemWrite('App/Test', 'TestString', 's', 'eins');
    $rm->systemWrite('App/Test', 'TestFloat', 'f', 1.1);
    $rm->systemWrite('App/Test', 'TestDate', 'd', time());
```

Read a key
----------

```php
    $rm = $this->get('registry');

    $value = $rm->systemRead('App/Test', 'TestString', 's');

    $value = $rm->systemReadDefault('App/Test', 'TestString', 's', 'I am a the default string');
```

Delete a key
------------

```php
    $rm = $this->get('registry');
    $rm->systemDelete('App/Test', 'TestDate', 'd');
```

[Return to the index.](index.md)
