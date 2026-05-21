<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251211044610 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE appointment ADD phone_number VARCHAR(20) DEFAULT NULL, ADD email VARCHAR(255) DEFAULT NULL, ADD address LONGTEXT DEFAULT NULL, ADD emergency_contact_name VARCHAR(255) DEFAULT NULL, ADD emergency_contact_phone VARCHAR(20) DEFAULT NULL, ADD emergency_contact_authorized TINYINT DEFAULT NULL, ADD reason_for_visit LONGTEXT DEFAULT NULL, ADD requested_services JSON DEFAULT NULL, ADD is_spayed_neutered TINYINT DEFAULT NULL, ADD pet_sex VARCHAR(20) DEFAULT NULL, ADD pet_color VARCHAR(100) DEFAULT NULL, ADD pet_identifying_marks VARCHAR(255) DEFAULT NULL, ADD vaccination_status VARCHAR(50) DEFAULT NULL, ADD microchip_number VARCHAR(100) DEFAULT NULL, CHANGE status status VARCHAR(50) NOT NULL, CHANGE client_name client_name VARCHAR(255) NOT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE appointment DROP phone_number, DROP email, DROP address, DROP emergency_contact_name, DROP emergency_contact_phone, DROP emergency_contact_authorized, DROP reason_for_visit, DROP requested_services, DROP is_spayed_neutered, DROP pet_sex, DROP pet_color, DROP pet_identifying_marks, DROP vaccination_status, DROP microchip_number, CHANGE client_name client_name VARCHAR(255) DEFAULT NULL, CHANGE status status VARCHAR(255) NOT NULL');
    }
}
