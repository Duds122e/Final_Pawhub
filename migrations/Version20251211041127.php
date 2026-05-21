<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251211041127 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE adoption_request ADD household_member LONGTEXT DEFAULT NULL, DROP household_members, DROP current_pets, DROP previous_pets, DROP spay_neuter, DROP pet_experience, CHANGE home_agreement home_agreement LONGTEXT DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE adoption_request ADD household_members VARCHAR(255) DEFAULT NULL, ADD current_pets VARCHAR(255) DEFAULT NULL, ADD previous_pets VARCHAR(255) DEFAULT NULL, ADD spay_neuter VARCHAR(255) DEFAULT NULL, ADD pet_experience VARCHAR(255) DEFAULT NULL, DROP household_member, CHANGE home_agreement home_agreement VARCHAR(255) DEFAULT NULL');
    }
}
