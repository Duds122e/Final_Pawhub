<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251211044123 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE adoption_request ADD daily_schedule VARCHAR(255) DEFAULT NULL, ADD financials VARCHAR(255) DEFAULT NULL, ADD contingency_plan VARCHAR(255) DEFAULT NULL, DROP pet_care_commitment');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE adoption_request ADD pet_care_commitment LONGTEXT DEFAULT NULL, DROP daily_schedule, DROP financials, DROP contingency_plan');
    }
}
