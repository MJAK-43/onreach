<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260612100000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Sprint candidat — statuts Paris-Saclay, dates Campus France / Parcoursup';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE campus_france_applications ADD updated_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NOW() NOT NULL');
        $this->addSql('ALTER TABLE campus_france_applications ALTER updated_at DROP DEFAULT');

        $this->addSql("ALTER TABLE paris_saclay_applications ADD status VARCHAR(255) DEFAULT 'draft' NOT NULL");
        $this->addSql('ALTER TABLE paris_saclay_applications ALTER status DROP DEFAULT');

        $this->addSql('ALTER TABLE parcoursup_wishes ADD submitted_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL');
        $this->addSql("UPDATE parcoursup_wishes SET status = 'brouillon' WHERE status = 'pending'");
        $this->addSql("UPDATE parcoursup_wishes SET status = 'soumis' WHERE status = 'submitted'");
        $this->addSql("UPDATE parcoursup_wishes SET status = 'accepte' WHERE status = 'accepted'");
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE campus_france_applications DROP updated_at');
        $this->addSql('ALTER TABLE paris_saclay_applications DROP status');
        $this->addSql('ALTER TABLE parcoursup_wishes DROP submitted_at');
    }
}
