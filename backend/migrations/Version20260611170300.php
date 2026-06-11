<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260611170300 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Sprint 2 — Module Candidate (Dossier Étudiant Unifié)';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE candidates (id UUID NOT NULL, reference_number VARCHAR(30) NOT NULL, first_name VARCHAR(100) NOT NULL, last_name VARCHAR(100) NOT NULL, gender VARCHAR(20) DEFAULT NULL, date_of_birth DATE DEFAULT NULL, place_of_birth VARCHAR(150) DEFAULT NULL, nationality VARCHAR(100) NOT NULL, phone VARCHAR(30) DEFAULT NULL, email VARCHAR(180) NOT NULL, address VARCHAR(255) DEFAULT NULL, city VARCHAR(100) DEFAULT NULL, country VARCHAR(100) DEFAULT NULL, status VARCHAR(255) NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, assigned_counselor_id UUID DEFAULT NULL, PRIMARY KEY (id))');
        $this->addSql('CREATE UNIQUE INDEX uniq_candidate_reference ON candidates (reference_number)');
        $this->addSql('CREATE UNIQUE INDEX uniq_candidate_email ON candidates (email)');
        $this->addSql('CREATE INDEX IDX_6A77F80C4658E0CE ON candidates (assigned_counselor_id)');
        $this->addSql('ALTER TABLE candidates ADD CONSTRAINT FK_6A77F80C4658E0CE FOREIGN KEY (assigned_counselor_id) REFERENCES users (id) ON DELETE SET NULL NOT DEFERRABLE INITIALLY IMMEDIATE');

        $this->addSql('CREATE TABLE academic_profiles (id UUID NOT NULL, highest_diploma VARCHAR(150) DEFAULT NULL, institution_name VARCHAR(255) DEFAULT NULL, graduation_year INT DEFAULT NULL, overall_average NUMERIC(5, 2) DEFAULT NULL, ranking VARCHAR(100) DEFAULT NULL, specialty VARCHAR(150) DEFAULT NULL, academic_achievements TEXT DEFAULT NULL, candidate_id UUID NOT NULL, PRIMARY KEY (id))');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_B4000D2791BD8781 ON academic_profiles (candidate_id)');
        $this->addSql('ALTER TABLE academic_profiles ADD CONSTRAINT FK_B4000D2791BD8781 FOREIGN KEY (candidate_id) REFERENCES candidates (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');

        $this->addSql('CREATE TABLE academic_records (id UUID NOT NULL, diploma VARCHAR(150) NOT NULL, institution VARCHAR(255) NOT NULL, year INT NOT NULL, average NUMERIC(5, 2) DEFAULT NULL, ranking VARCHAR(100) DEFAULT NULL, specialty VARCHAR(150) DEFAULT NULL, achievements TEXT DEFAULT NULL, academic_profile_id UUID NOT NULL, PRIMARY KEY (id))');
        $this->addSql('CREATE INDEX IDX_191392E02A1AA20C ON academic_records (academic_profile_id)');
        $this->addSql('ALTER TABLE academic_records ADD CONSTRAINT FK_191392E02A1AA20C FOREIGN KEY (academic_profile_id) REFERENCES academic_profiles (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');

        $this->addSql('CREATE TABLE language_profiles (id UUID NOT NULL, french_level VARCHAR(20) DEFAULT NULL, english_level VARCHAR(20) DEFAULT NULL, candidate_id UUID NOT NULL, PRIMARY KEY (id))');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_E792DD0A91BD8781 ON language_profiles (candidate_id)');
        $this->addSql('ALTER TABLE language_profiles ADD CONSTRAINT FK_E792DD0A91BD8781 FOREIGN KEY (candidate_id) REFERENCES candidates (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');

        $this->addSql('CREATE TABLE language_certificates (id UUID NOT NULL, type VARCHAR(255) NOT NULL, score VARCHAR(50) DEFAULT NULL, issue_date DATE DEFAULT NULL, expiration_date DATE DEFAULT NULL, language_profile_id UUID NOT NULL, PRIMARY KEY (id))');
        $this->addSql('CREATE INDEX IDX_A82A6F8A8CE379A5 ON language_certificates (language_profile_id)');
        $this->addSql('ALTER TABLE language_certificates ADD CONSTRAINT FK_A82A6F8A8CE379A5 FOREIGN KEY (language_profile_id) REFERENCES language_profiles (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');

        $this->addSql('CREATE TABLE professional_profiles (id UUID NOT NULL, current_occupation VARCHAR(150) DEFAULT NULL, years_experience INT DEFAULT NULL, professional_summary TEXT DEFAULT NULL, candidate_id UUID NOT NULL, PRIMARY KEY (id))');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_18B65F8D91BD8781 ON professional_profiles (candidate_id)');
        $this->addSql('ALTER TABLE professional_profiles ADD CONSTRAINT FK_18B65F8D91BD8781 FOREIGN KEY (candidate_id) REFERENCES candidates (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');

        $this->addSql('CREATE TABLE professional_experiences (id UUID NOT NULL, company VARCHAR(255) NOT NULL, position VARCHAR(150) NOT NULL, start_date DATE NOT NULL, end_date DATE DEFAULT NULL, description TEXT DEFAULT NULL, professional_profile_id UUID NOT NULL, PRIMARY KEY (id))');
        $this->addSql('CREATE INDEX IDX_30886820FB976A01 ON professional_experiences (professional_profile_id)');
        $this->addSql('ALTER TABLE professional_experiences ADD CONSTRAINT FK_30886820FB976A01 FOREIGN KEY (professional_profile_id) REFERENCES professional_profiles (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');

        $this->addSql('CREATE TABLE financing_profiles (id UUID NOT NULL, type VARCHAR(255) NOT NULL, candidate_id UUID NOT NULL, PRIMARY KEY (id))');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_4D1DC10591BD8781 ON financing_profiles (candidate_id)');
        $this->addSql('ALTER TABLE financing_profiles ADD CONSTRAINT FK_4D1DC10591BD8781 FOREIGN KEY (candidate_id) REFERENCES candidates (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');

        $this->addSql('CREATE TABLE guarantors (id UUID NOT NULL, full_name VARCHAR(150) NOT NULL, profession VARCHAR(150) DEFAULT NULL, monthly_income NUMERIC(12, 2) DEFAULT NULL, phone VARCHAR(30) DEFAULT NULL, email VARCHAR(180) DEFAULT NULL, address VARCHAR(255) DEFAULT NULL, financing_profile_id UUID NOT NULL, PRIMARY KEY (id))');
        $this->addSql('CREATE INDEX IDX_9B4A05351F7A93AA ON guarantors (financing_profile_id)');
        $this->addSql('ALTER TABLE guarantors ADD CONSTRAINT FK_9B4A05351F7A93AA FOREIGN KEY (financing_profile_id) REFERENCES financing_profiles (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');

        $this->addSql('CREATE TABLE candidate_documents (id UUID NOT NULL, type VARCHAR(255) NOT NULL, status VARCHAR(255) NOT NULL, filename VARCHAR(255) DEFAULT NULL, original_filename VARCHAR(255) DEFAULT NULL, mime_type VARCHAR(100) DEFAULT NULL, size INT DEFAULT NULL, storage_path VARCHAR(500) DEFAULT NULL, uploaded_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, validated_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, rejection_reason TEXT DEFAULT NULL, candidate_id UUID NOT NULL, validated_by_id UUID DEFAULT NULL, PRIMARY KEY (id))');
        $this->addSql('CREATE INDEX IDX_BDCBAFA491BD8781 ON candidate_documents (candidate_id)');
        $this->addSql('CREATE INDEX IDX_BDCBAFA4C69DE5E5 ON candidate_documents (validated_by_id)');
        $this->addSql('ALTER TABLE candidate_documents ADD CONSTRAINT FK_BDCBAFA491BD8781 FOREIGN KEY (candidate_id) REFERENCES candidates (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE candidate_documents ADD CONSTRAINT FK_BDCBAFA4C69DE5E5 FOREIGN KEY (validated_by_id) REFERENCES users (id) ON DELETE SET NULL NOT DEFERRABLE INITIALLY IMMEDIATE');

        $this->addSql('CREATE TABLE candidate_notes (id UUID NOT NULL, title VARCHAR(200) NOT NULL, content TEXT NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, candidate_id UUID NOT NULL, author_id UUID NOT NULL, PRIMARY KEY (id))');
        $this->addSql('CREATE INDEX IDX_39FB4D7091BD8781 ON candidate_notes (candidate_id)');
        $this->addSql('CREATE INDEX IDX_39FB4D70F675F31B ON candidate_notes (author_id)');
        $this->addSql('ALTER TABLE candidate_notes ADD CONSTRAINT FK_39FB4D7091BD8781 FOREIGN KEY (candidate_id) REFERENCES candidates (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE candidate_notes ADD CONSTRAINT FK_39FB4D70F675F31B FOREIGN KEY (author_id) REFERENCES users (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');

        $this->addSql('CREATE TABLE candidate_timeline_entries (id UUID NOT NULL, action VARCHAR(100) NOT NULL, description TEXT DEFAULT NULL, metadata JSON DEFAULT NULL, occurred_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, candidate_id UUID NOT NULL, actor_id UUID DEFAULT NULL, PRIMARY KEY (id))');
        $this->addSql('CREATE INDEX IDX_38174EBB91BD8781 ON candidate_timeline_entries (candidate_id)');
        $this->addSql('CREATE INDEX IDX_38174EBB10DAF24A ON candidate_timeline_entries (actor_id)');
        $this->addSql('ALTER TABLE candidate_timeline_entries ADD CONSTRAINT FK_38174EBB91BD8781 FOREIGN KEY (candidate_id) REFERENCES candidates (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE candidate_timeline_entries ADD CONSTRAINT FK_38174EBB10DAF24A FOREIGN KEY (actor_id) REFERENCES users (id) ON DELETE SET NULL NOT DEFERRABLE INITIALLY IMMEDIATE');

        $this->addSql('CREATE TABLE campus_france_applications (id UUID NOT NULL, study_project TEXT DEFAULT NULL, professional_project TEXT DEFAULT NULL, target_universities JSON DEFAULT NULL, target_programs JSON DEFAULT NULL, status VARCHAR(255) NOT NULL, candidate_id UUID NOT NULL, PRIMARY KEY (id))');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_282F1BAF91BD8781 ON campus_france_applications (candidate_id)');
        $this->addSql('ALTER TABLE campus_france_applications ADD CONSTRAINT FK_282F1BAF91BD8781 FOREIGN KEY (candidate_id) REFERENCES candidates (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');

        $this->addSql('CREATE TABLE parcoursup_applications (id UUID NOT NULL, ine_number VARCHAR(20) DEFAULT NULL, high_school VARCHAR(255) DEFAULT NULL, specialties JSON DEFAULT NULL, activities JSON DEFAULT NULL, interests JSON DEFAULT NULL, motivation_project TEXT DEFAULT NULL, candidate_id UUID NOT NULL, PRIMARY KEY (id))');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_535591B091BD8781 ON parcoursup_applications (candidate_id)');
        $this->addSql('ALTER TABLE parcoursup_applications ADD CONSTRAINT FK_535591B091BD8781 FOREIGN KEY (candidate_id) REFERENCES candidates (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');

        $this->addSql('CREATE TABLE parcoursup_wishes (id UUID NOT NULL, rank INT NOT NULL, university VARCHAR(255) NOT NULL, program VARCHAR(255) NOT NULL, status VARCHAR(50) NOT NULL, parcoursup_application_id UUID NOT NULL, PRIMARY KEY (id))');
        $this->addSql('CREATE INDEX IDX_E57A42AEEDA386EB ON parcoursup_wishes (parcoursup_application_id)');
        $this->addSql('ALTER TABLE parcoursup_wishes ADD CONSTRAINT FK_E57A42AEEDA386EB FOREIGN KEY (parcoursup_application_id) REFERENCES parcoursup_applications (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');

        $this->addSql('CREATE TABLE paris_saclay_applications (id UUID NOT NULL, degree_level VARCHAR(255) DEFAULT NULL, research_project TEXT DEFAULT NULL, publications JSON DEFAULT NULL, internship_reports JSON DEFAULT NULL, candidate_id UUID NOT NULL, PRIMARY KEY (id))');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_6EF036EF91BD8781 ON paris_saclay_applications (candidate_id)');
        $this->addSql('ALTER TABLE paris_saclay_applications ADD CONSTRAINT FK_6EF036EF91BD8781 FOREIGN KEY (candidate_id) REFERENCES candidates (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');

        $this->addSql('CREATE TABLE checklist_templates (id UUID NOT NULL, code VARCHAR(50) NOT NULL, name VARCHAR(150) NOT NULL, application_type VARCHAR(255) NOT NULL, PRIMARY KEY (id))');
        $this->addSql('CREATE UNIQUE INDEX uniq_checklist_template_code ON checklist_templates (code)');

        $this->addSql('CREATE TABLE checklist_items (id UUID NOT NULL, document_type VARCHAR(255) NOT NULL, label VARCHAR(200) NOT NULL, required BOOLEAN NOT NULL, sort_order INT NOT NULL, template_id UUID NOT NULL, PRIMARY KEY (id))');
        $this->addSql('CREATE INDEX IDX_DFF66E935DA0FB8 ON checklist_items (template_id)');
        $this->addSql('ALTER TABLE checklist_items ADD CONSTRAINT FK_DFF66E935DA0FB8 FOREIGN KEY (template_id) REFERENCES checklist_templates (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');

        $this->addSql('CREATE TABLE checklist_progress (id UUID NOT NULL, completed BOOLEAN NOT NULL, completed_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, candidate_id UUID NOT NULL, checklist_item_id UUID NOT NULL, PRIMARY KEY (id))');
        $this->addSql('CREATE INDEX IDX_7F058E8491BD8781 ON checklist_progress (candidate_id)');
        $this->addSql('CREATE INDEX IDX_7F058E847E0892A4 ON checklist_progress (checklist_item_id)');
        $this->addSql('CREATE UNIQUE INDEX uniq_checklist_progress_candidate_item ON checklist_progress (candidate_id, checklist_item_id)');
        $this->addSql('ALTER TABLE checklist_progress ADD CONSTRAINT FK_7F058E8491BD8781 FOREIGN KEY (candidate_id) REFERENCES candidates (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE checklist_progress ADD CONSTRAINT FK_7F058E847E0892A4 FOREIGN KEY (checklist_item_id) REFERENCES checklist_items (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE checklist_progress DROP CONSTRAINT FK_7F058E847E0892A4');
        $this->addSql('ALTER TABLE checklist_progress DROP CONSTRAINT FK_7F058E8491BD8781');
        $this->addSql('ALTER TABLE checklist_items DROP CONSTRAINT FK_DFF66E935DA0FB8');
        $this->addSql('ALTER TABLE paris_saclay_applications DROP CONSTRAINT FK_6EF036EF91BD8781');
        $this->addSql('ALTER TABLE parcoursup_wishes DROP CONSTRAINT FK_E57A42AEEDA386EB');
        $this->addSql('ALTER TABLE parcoursup_applications DROP CONSTRAINT FK_535591B091BD8781');
        $this->addSql('ALTER TABLE campus_france_applications DROP CONSTRAINT FK_282F1BAF91BD8781');
        $this->addSql('ALTER TABLE candidate_timeline_entries DROP CONSTRAINT FK_38174EBB10DAF24A');
        $this->addSql('ALTER TABLE candidate_timeline_entries DROP CONSTRAINT FK_38174EBB91BD8781');
        $this->addSql('ALTER TABLE candidate_notes DROP CONSTRAINT FK_39FB4D70F675F31B');
        $this->addSql('ALTER TABLE candidate_notes DROP CONSTRAINT FK_39FB4D7091BD8781');
        $this->addSql('ALTER TABLE candidate_documents DROP CONSTRAINT FK_BDCBAFA4C69DE5E5');
        $this->addSql('ALTER TABLE candidate_documents DROP CONSTRAINT FK_BDCBAFA491BD8781');
        $this->addSql('ALTER TABLE guarantors DROP CONSTRAINT FK_9B4A05351F7A93AA');
        $this->addSql('ALTER TABLE financing_profiles DROP CONSTRAINT FK_4D1DC10591BD8781');
        $this->addSql('ALTER TABLE professional_experiences DROP CONSTRAINT FK_30886820FB976A01');
        $this->addSql('ALTER TABLE professional_profiles DROP CONSTRAINT FK_18B65F8D91BD8781');
        $this->addSql('ALTER TABLE language_certificates DROP CONSTRAINT FK_A82A6F8A8CE379A5');
        $this->addSql('ALTER TABLE language_profiles DROP CONSTRAINT FK_E792DD0A91BD8781');
        $this->addSql('ALTER TABLE academic_records DROP CONSTRAINT FK_191392E02A1AA20C');
        $this->addSql('ALTER TABLE academic_profiles DROP CONSTRAINT FK_B4000D2791BD8781');
        $this->addSql('ALTER TABLE candidates DROP CONSTRAINT FK_6A77F80C4658E0CE');
        $this->addSql('DROP TABLE checklist_progress');
        $this->addSql('DROP TABLE checklist_items');
        $this->addSql('DROP TABLE checklist_templates');
        $this->addSql('DROP TABLE paris_saclay_applications');
        $this->addSql('DROP TABLE parcoursup_wishes');
        $this->addSql('DROP TABLE parcoursup_applications');
        $this->addSql('DROP TABLE campus_france_applications');
        $this->addSql('DROP TABLE candidate_timeline_entries');
        $this->addSql('DROP TABLE candidate_notes');
        $this->addSql('DROP TABLE candidate_documents');
        $this->addSql('DROP TABLE guarantors');
        $this->addSql('DROP TABLE financing_profiles');
        $this->addSql('DROP TABLE professional_experiences');
        $this->addSql('DROP TABLE professional_profiles');
        $this->addSql('DROP TABLE language_certificates');
        $this->addSql('DROP TABLE language_profiles');
        $this->addSql('DROP TABLE academic_records');
        $this->addSql('DROP TABLE academic_profiles');
        $this->addSql('DROP TABLE candidates');
    }
}
