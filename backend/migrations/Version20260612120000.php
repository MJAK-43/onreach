<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260612120000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Sprint Mon dossier — champs profil candidat, financement, documents versionnés';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE candidates ADD marital_status VARCHAR(30) DEFAULT NULL');
        $this->addSql('ALTER TABLE candidates ADD passport_number VARCHAR(50) DEFAULT NULL');
        $this->addSql('ALTER TABLE candidates ADD passport_issued_at DATE DEFAULT NULL');
        $this->addSql('ALTER TABLE candidates ADD passport_expires_at DATE DEFAULT NULL');
        $this->addSql('ALTER TABLE candidates ADD passport_country VARCHAR(100) DEFAULT NULL');
        $this->addSql('ALTER TABLE candidates ADD identity_card_number VARCHAR(50) DEFAULT NULL');
        $this->addSql('ALTER TABLE candidates ADD postal_code VARCHAR(20) DEFAULT NULL');
        $this->addSql('ALTER TABLE candidates ADD region VARCHAR(100) DEFAULT NULL');
        $this->addSql('ALTER TABLE candidates ADD whatsapp VARCHAR(30) DEFAULT NULL');
        $this->addSql('ALTER TABLE candidates ADD secondary_email VARCHAR(180) DEFAULT NULL');
        $this->addSql('ALTER TABLE candidates ADD study_domain VARCHAR(150) DEFAULT NULL');
        $this->addSql('ALTER TABLE candidates ADD study_specialty VARCHAR(150) DEFAULT NULL');
        $this->addSql('ALTER TABLE candidates ADD study_level VARCHAR(50) DEFAULT NULL');
        $this->addSql('ALTER TABLE candidates ADD study_target_country VARCHAR(100) DEFAULT NULL');
        $this->addSql('ALTER TABLE candidates ADD study_description TEXT DEFAULT NULL');
        $this->addSql('ALTER TABLE candidates ADD study_universities JSON DEFAULT NULL');
        $this->addSql('ALTER TABLE candidates ADD career_target_job VARCHAR(150) DEFAULT NULL');
        $this->addSql('ALTER TABLE candidates ADD career_objectives TEXT DEFAULT NULL');
        $this->addSql('ALTER TABLE candidates ADD career_sector VARCHAR(150) DEFAULT NULL');
        $this->addSql('ALTER TABLE candidates ADD career_description TEXT DEFAULT NULL');

        $this->addSql('ALTER TABLE academic_records ADD diploma_type VARCHAR(50) DEFAULT NULL');
        $this->addSql('ALTER TABLE academic_records ADD country VARCHAR(100) DEFAULT NULL');
        $this->addSql('ALTER TABLE academic_records ADD mention VARCHAR(100) DEFAULT NULL');

        $this->addSql('ALTER TABLE financing_profiles ADD available_budget NUMERIC(12, 2) DEFAULT NULL');
        $this->addSql('ALTER TABLE financing_profiles ADD planned_amount NUMERIC(12, 2) DEFAULT NULL');
        $this->addSql('ALTER TABLE financing_profiles ADD description TEXT DEFAULT NULL');

        $this->addSql('ALTER TABLE guarantors ADD first_name VARCHAR(100) DEFAULT NULL');
        $this->addSql('ALTER TABLE guarantors ADD last_name VARCHAR(100) DEFAULT NULL');
        $this->addSql('ALTER TABLE guarantors ADD employer VARCHAR(150) DEFAULT NULL');

        $this->addSql('ALTER TABLE language_profiles ADD other_languages JSON DEFAULT NULL');

        $this->addSql('ALTER TABLE candidate_documents ADD version INT DEFAULT 1 NOT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE candidate_documents DROP version');
        $this->addSql('ALTER TABLE language_profiles DROP other_languages');
        $this->addSql('ALTER TABLE guarantors DROP first_name, DROP last_name, DROP employer');
        $this->addSql('ALTER TABLE financing_profiles DROP available_budget, DROP planned_amount, DROP description');
        $this->addSql('ALTER TABLE academic_records DROP diploma_type, DROP country, DROP mention');
        $this->addSql('ALTER TABLE candidates DROP marital_status, DROP passport_number, DROP passport_issued_at, DROP passport_expires_at, DROP passport_country, DROP identity_card_number, DROP postal_code, DROP region, DROP whatsapp, DROP secondary_email, DROP study_domain, DROP study_specialty, DROP study_level, DROP study_target_country, DROP study_description, DROP study_universities, DROP career_target_job, DROP career_objectives, DROP career_sector, DROP career_description');
    }
}
