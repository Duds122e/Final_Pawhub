<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251211050731 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE appointment ADD pet_name VARCHAR(100) DEFAULT NULL, ADD pet_species VARCHAR(50) DEFAULT NULL, ADD pet_breed VARCHAR(100) DEFAULT NULL, ADD pet_date_of_birth DATE DEFAULT NULL, ADD pet_sex VARCHAR(10) DEFAULT NULL, ADD pet_neutered_status VARCHAR(20) DEFAULT NULL, ADD pet_weight DOUBLE PRECISION DEFAULT NULL, ADD pet_color_marks VARCHAR(255) DEFAULT NULL, ADD pet_vaccination_status VARCHAR(50) DEFAULT NULL, ADD pet_microchip_number VARCHAR(100) DEFAULT NULL, CHANGE owner_address owner_address VARCHAR(255) DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE appointment DROP pet_name, DROP pet_species, DROP pet_breed, DROP pet_date_of_birth, DROP pet_sex, DROP pet_neutered_status, DROP pet_weight, DROP pet_color_marks, DROP pet_vaccination_status, DROP pet_microchip_number, CHANGE owner_address owner_address VARCHAR(512) DEFAULT NULL');
    }
}
