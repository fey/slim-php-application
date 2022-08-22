<?php

declare(strict_types=1);

use Phoenix\Database\Element\Column;
use Phoenix\Migration\AbstractMigration;

final class DropPasswordDigestColumnUsersTable extends AbstractMigration
{
    protected function up(): void
    {
        $this->table('users')
            ->dropColumn('password_digest');
    }

    protected function down(): void
    {
        $this->table('users')
            ->addColumn('password_digest', Column::TYPE_STRING);
    }
}
