<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260611230000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Module rendez-vous — créneaux conseiller et réservations candidat';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE counselor_availability_slots (id UUID NOT NULL, starts_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, ends_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, status VARCHAR(255) NOT NULL, booked_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, subject VARCHAR(200) DEFAULT NULL, counselor_id UUID NOT NULL, candidate_id UUID DEFAULT NULL, PRIMARY KEY (id))');
        $this->addSql('CREATE INDEX IDX_SLOT_COUNSELOR ON counselor_availability_slots (counselor_id)');
        $this->addSql('CREATE INDEX IDX_SLOT_CANDIDATE ON counselor_availability_slots (candidate_id)');
        $this->addSql('CREATE INDEX IDX_SLOT_START ON counselor_availability_slots (starts_at)');
        $this->addSql('CREATE UNIQUE INDEX uniq_counselor_slot_start ON counselor_availability_slots (counselor_id, starts_at)');
        $this->addSql('ALTER TABLE counselor_availability_slots ADD CONSTRAINT FK_SLOT_COUNSELOR FOREIGN KEY (counselor_id) REFERENCES users (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE counselor_availability_slots ADD CONSTRAINT FK_SLOT_CANDIDATE FOREIGN KEY (candidate_id) REFERENCES candidates (id) ON DELETE SET NULL NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE counselor_availability_slots DROP CONSTRAINT FK_SLOT_COUNSELOR');
        $this->addSql('ALTER TABLE counselor_availability_slots DROP CONSTRAINT FK_SLOT_CANDIDATE');
        $this->addSql('DROP TABLE counselor_availability_slots');
    }
}
