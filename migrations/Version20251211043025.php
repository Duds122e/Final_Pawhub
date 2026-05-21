<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251211043025 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE adoption_request ADD current_pets VARCHAR(255) DEFAULT NULL, ADD previous_pets VARCHAR(255) DEFAULT NULL, ADD spay_neuter VARCHAR(255) DEFAULT NULL, ADD pet_experience VARCHAR(255) DEFAULT NULL, DROP pet_history');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE adoption_request ADD pet_history LONGTEXT DEFAULT NULL, DROP current_pets, DROP previous_pets, DROP spay_neuter, DROP pet_experience');
    }
}
