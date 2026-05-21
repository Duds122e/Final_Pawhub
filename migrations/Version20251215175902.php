<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251215175902 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE appointment ADD location VARCHAR(255) DEFAULT NULL, ADD reason VARCHAR(255) DEFAULT NULL, ADD owner_name VARCHAR(255) DEFAULT NULL, ADD contact_phone VARCHAR(50) DEFAULT NULL, ADD address VARCHAR(255) DEFAULT NULL, ADD emergency_contact VARCHAR(255) DEFAULT NULL, ADD pet_species VARCHAR(100) DEFAULT NULL, ADD pet_breed VARCHAR(100) DEFAULT NULL, ADD pet_color VARCHAR(100) DEFAULT NULL, ADD pet_sex VARCHAR(100) DEFAULT NULL, ADD pet_spay_neuter VARCHAR(100) DEFAULT NULL, ADD pet_weight VARCHAR(100) DEFAULT NULL, ADD pet_microchip VARCHAR(100) DEFAULT NULL, ADD pet_dob DATE DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE appointment DROP location, DROP reason, DROP owner_name, DROP contact_phone, DROP address, DROP emergency_contact, DROP pet_species, DROP pet_breed, DROP pet_color, DROP pet_sex, DROP pet_spay_neuter, DROP pet_weight, DROP pet_microchip, DROP pet_dob');
    }
}
