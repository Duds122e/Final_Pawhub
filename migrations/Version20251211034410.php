<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251211034410 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE adoption_request ADD pet_id INT DEFAULT NULL, ADD user_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE adoption_request ADD CONSTRAINT FK_410896EE966F7FB6 FOREIGN KEY (pet_id) REFERENCES pet (id)');
        $this->addSql('ALTER TABLE adoption_request ADD CONSTRAINT FK_410896EEA76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('CREATE INDEX IDX_410896EE966F7FB6 ON adoption_request (pet_id)');
        $this->addSql('CREATE INDEX IDX_410896EEA76ED395 ON adoption_request (user_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE adoption_request DROP FOREIGN KEY FK_410896EE966F7FB6');
        $this->addSql('ALTER TABLE adoption_request DROP FOREIGN KEY FK_410896EEA76ED395');
        $this->addSql('DROP INDEX IDX_410896EE966F7FB6 ON adoption_request');
        $this->addSql('DROP INDEX IDX_410896EEA76ED395 ON adoption_request');
        $this->addSql('ALTER TABLE adoption_request DROP pet_id, DROP user_id');
    }
}
