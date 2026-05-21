<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260407130000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add email column to user table (late fix)';
    }

    public function up(Schema $schema): void
    {
        $table = $schema->getTable('user');

        if (!$table->hasColumn('email')) {
            $this->addSql('ALTER TABLE user ADD email VARCHAR(180) DEFAULT NULL');
        }

        // Avoid errors if index already exists in some environments.
        $emailIndexName = 'UNIQ_8D93D649E7927C74';
        if (!$table->hasIndex($emailIndexName)) {
            $this->addSql(sprintf('CREATE UNIQUE INDEX %s ON user (email)', $emailIndexName));
        }
    }

    public function down(Schema $schema): void
    {
        $table = $schema->getTable('user');

        $emailIndexName = 'UNIQ_8D93D649E7927C74';
        if ($table->hasIndex($emailIndexName)) {
            $this->addSql(sprintf('DROP INDEX %s ON user', $emailIndexName));
        }

        if ($table->hasColumn('email')) {
            $this->addSql('ALTER TABLE user DROP email');
        }
    }
}

