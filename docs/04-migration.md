# Migration: `value` becomes `NOT NULL DEFAULT ''`

Up to and including the previous release, the `value` column of the `registry`
and `system` tables was mapped as `nullable: true`, while the PHP property was a
non-nullable `string`. A `NULL` value read from the database therefore produced a
`TypeError`. As of this release the column is mapped as:

```php
#[ORM\Column(name: 'value', type: 'text', nullable: false, options: ['default' => ''])]
private string $value = '';
```

Values are always persisted as strings; the empty string is used instead of
`NULL`. Existing schemas must be migrated.

> Table names: the registry table is `registry`; the system table is a reserved
> word and is quoted — `` `system` `` on MariaDB/MySQL, `"system"` on PostgreSQL.
> The unique constraints `uix_userid_key_name` (registry) and `uix_key_name`
> (system) are unaffected by this change.

## PostgreSQL (18)

```sql
-- registry
UPDATE registry SET value = '' WHERE value IS NULL;
ALTER TABLE registry ALTER COLUMN value SET DEFAULT '';
ALTER TABLE registry ALTER COLUMN value SET NOT NULL;

-- system (reserved word -> double-quoted)
UPDATE "system" SET value = '' WHERE value IS NULL;
ALTER TABLE "system" ALTER COLUMN value SET DEFAULT '';
ALTER TABLE "system" ALTER COLUMN value SET NOT NULL;
```

## MariaDB / MySQL

```sql
-- registry
UPDATE registry SET value = '' WHERE value IS NULL;
ALTER TABLE registry MODIFY value LONGTEXT NOT NULL DEFAULT '';

-- system (reserved word -> backtick-quoted)
UPDATE `system` SET value = '' WHERE value IS NULL;
ALTER TABLE `system` MODIFY value LONGTEXT NOT NULL DEFAULT '';
```

## Doctrine migration (doctrine/migrations)

If your application uses `doctrine/migrations`, generate a migration with
`bin/console doctrine:migrations:diff` after upgrading the bundle, or copy the
template below. It is written platform-agnostically by delegating the column
change to the connected platform; only the data backfill is issued as raw SQL.

```php
<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class VersionRegistryValueNotNull extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'registry/system: value NOT NULL DEFAULT ""';
    }

    public function up(Schema $schema): void
    {
        $system = $this->connection->getDatabasePlatform()->quoteIdentifier('system');

        // backfill existing NULLs before tightening the constraint
        $this->addSql("UPDATE registry SET value = '' WHERE value IS NULL");
        $this->addSql("UPDATE {$system} SET value = '' WHERE value IS NULL");

        $registry = $schema->getTable('registry');
        $registry->getColumn('value')->setNotnull(true)->setDefault('');

        $system = $schema->getTable('system');
        $system->getColumn('value')->setNotnull(true)->setDefault('');
    }

    public function down(Schema $schema): void
    {
        $schema->getTable('registry')->getColumn('value')->setNotnull(false)->setDefault(null);
        $schema->getTable('system')->getColumn('value')->setNotnull(false)->setDefault(null);
    }
}
```
