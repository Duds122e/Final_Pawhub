<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260522120000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Allow nullable service.description to match entity';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE service CHANGE description description LONGTEXT DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE service CHANGE description description LONGTEXT NOT NULL');
    }
}
