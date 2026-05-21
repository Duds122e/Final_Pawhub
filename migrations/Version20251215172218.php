<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251215172218 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE appointment DROP FOREIGN KEY `FK_FE38F844ED5CA9E6`');
        $this->addSql('DROP INDEX IDX_FE38F844ED5CA9E6 ON appointment');
        $this->addSql('ALTER TABLE appointment ADD date_time DATETIME NOT NULL, ADD user_id INT NOT NULL, DROP status, DROP service_id, DROP date, DROP client_name, DROP owner_contact, DROP owner_address, DROP service_type, DROP pet_name, DROP pet_species, DROP pet_breed, DROP pet_date_of_birth, DROP pet_sex, DROP pet_neutered_status, DROP pet_weight, DROP pet_color_marks, DROP pet_vaccination_status, DROP pet_microchip_number, CHANGE pet_id pet_id INT NOT NULL');
        $this->addSql('ALTER TABLE appointment ADD CONSTRAINT FK_FE38F844A76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('CREATE INDEX IDX_FE38F844A76ED395 ON appointment (user_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE appointment DROP FOREIGN KEY FK_FE38F844A76ED395');
        $this->addSql('DROP INDEX IDX_FE38F844A76ED395 ON appointment');
        $this->addSql('ALTER TABLE appointment ADD status VARCHAR(255) NOT NULL, ADD service_id INT DEFAULT NULL, ADD date DATETIME DEFAULT NULL, ADD client_name VARCHAR(255) DEFAULT NULL, ADD owner_contact VARCHAR(255) DEFAULT NULL, ADD owner_address VARCHAR(255) DEFAULT NULL, ADD service_type VARCHAR(50) DEFAULT NULL, ADD pet_name VARCHAR(100) DEFAULT NULL, ADD pet_species VARCHAR(50) DEFAULT NULL, ADD pet_breed VARCHAR(100) DEFAULT NULL, ADD pet_date_of_birth DATE DEFAULT NULL, ADD pet_sex VARCHAR(10) DEFAULT NULL, ADD pet_neutered_status VARCHAR(20) DEFAULT NULL, ADD pet_weight DOUBLE PRECISION DEFAULT NULL, ADD pet_color_marks VARCHAR(255) DEFAULT NULL, ADD pet_vaccination_status VARCHAR(50) DEFAULT NULL, ADD pet_microchip_number VARCHAR(100) DEFAULT NULL, DROP date_time, DROP user_id, CHANGE pet_id pet_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE appointment ADD CONSTRAINT `FK_FE38F844ED5CA9E6` FOREIGN KEY (service_id) REFERENCES service (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
        $this->addSql('CREATE INDEX IDX_FE38F844ED5CA9E6 ON appointment (service_id)');
    }
}
