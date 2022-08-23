<?php

declare(strict_types=1);

use Phoenix\Database\Element\Column;
use Phoenix\Database\Element\Index;
use Phoenix\Migration\AbstractMigration;

final class CreateUsersTable extends AbstractMigration
{
    protected function up(): void
    {
        $this->table('users')
            ->addColumn('email', Column::TYPE_STRING)
            ->addColumn('first_name', Column::TYPE_STRING)
            ->addColumn('last_name', Column::TYPE_STRING)
            ->addColumn('created_at', Column::TYPE_DATETIME)
            ->addColumn('updated_at', Column::TYPE_DATETIME)
            ->addIndex('email', Index::TYPE_UNIQUE)
            ->create();
    }

    protected function down(): void
    {
        $this->table('users')
            ->drop();
    }
}
