<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260522100000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Restore appointment.service_id for catalog booking';
    }

    public function up(Schema $schema): void
    {
        $table = $schema->getTable('appointment');

        if (!$table->hasColumn('service_id')) {
            $this->addSql('ALTER TABLE appointment ADD service_id INT DEFAULT NULL');
            $this->addSql(
                'ALTER TABLE appointment ADD CONSTRAINT FK_APPOINTMENT_SERVICE FOREIGN KEY (service_id) REFERENCES service (id) ON DELETE SET NULL',
            );
            $this->addSql('CREATE INDEX IDX_APPOINTMENT_SERVICE ON appointment (service_id)');
        }
    }

    public function down(Schema $schema): void
    {
        $table = $schema->getTable('appointment');

        if ($table->hasColumn('service_id')) {
            $this->addSql('ALTER TABLE appointment DROP FOREIGN KEY FK_APPOINTMENT_SERVICE');
            $this->addSql('DROP INDEX IDX_APPOINTMENT_SERVICE ON appointment');
            $this->addSql('ALTER TABLE appointment DROP service_id');
        }
    }
}
