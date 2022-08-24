<?php

declare(strict_types=1);

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20220824165720 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        $table = $schema->createTable('users');
        $table->addColumn('email', 'string');
        $table->addColumn('first_name', 'string');
        $table->addColumn('last_name', 'string');
        $table->addColumn('created_at', 'datetime');
        $table->addColumn('updated_at', 'datetime');
    }

    public function down(Schema $schema): void
    {
        $schema->dropTable('users');
    }
}
