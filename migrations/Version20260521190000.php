<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260521190000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add is_verified and verification_token columns to user table';
    }

    public function up(Schema $schema): void
    {
        $table = $schema->getTable('user');

        if (!$table->hasColumn('is_verified')) {
            $this->addSql('ALTER TABLE user ADD is_verified TINYINT(1) DEFAULT 0 NOT NULL');
        }

        if (!$table->hasColumn('verification_token')) {
            $this->addSql('ALTER TABLE user ADD verification_token VARCHAR(64) DEFAULT NULL');
            $this->addSql('CREATE UNIQUE INDEX UNIQ_USER_VERIFICATION_TOKEN ON user (verification_token)');
        }
    }

    public function down(Schema $schema): void
    {
        $table = $schema->getTable('user');

        if ($table->hasIndex('UNIQ_USER_VERIFICATION_TOKEN')) {
            $this->addSql('DROP INDEX UNIQ_USER_VERIFICATION_TOKEN ON user');
        }

        if ($table->hasColumn('verification_token')) {
            $this->addSql('ALTER TABLE user DROP verification_token');
        }

        if ($table->hasColumn('is_verified')) {
            $this->addSql('ALTER TABLE user DROP is_verified');
        }
    }
}
