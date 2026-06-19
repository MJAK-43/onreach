<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260618100000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Phase 1 suivi candidatures — campagnes, parcours configurables, instances candidat';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE campaigns (id UUID NOT NULL, name VARCHAR(100) NOT NULL, year INT NOT NULL, start_date TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, end_date TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, active BOOLEAN NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE TABLE pathway_templates (id UUID NOT NULL, campaign_id UUID NOT NULL, code VARCHAR(255) NOT NULL, name VARCHAR(150) NOT NULL, eligible_study_types JSON NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_pathway_templates_campaign ON pathway_templates (campaign_id)');
        $this->addSql('CREATE UNIQUE INDEX uniq_pathway_template_campaign_code ON pathway_templates (campaign_id, code)');
        $this->addSql('CREATE TABLE pathway_stage_templates (id UUID NOT NULL, pathway_template_id UUID NOT NULL, title VARCHAR(200) NOT NULL, description TEXT DEFAULT NULL, sort_order INT NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_pathway_stage_templates_pathway ON pathway_stage_templates (pathway_template_id)');
        $this->addSql('CREATE TABLE pathway_sub_step_templates (id UUID NOT NULL, stage_template_id UUID NOT NULL, title VARCHAR(200) NOT NULL, description TEXT DEFAULT NULL, required BOOLEAN NOT NULL, default_due_offset_days INT DEFAULT NULL, sort_order INT NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_pathway_sub_step_templates_stage ON pathway_sub_step_templates (stage_template_id)');
        $this->addSql('CREATE TABLE pathway_settings (id UUID NOT NULL, pathway_code VARCHAR(255) NOT NULL, double_validation_enabled BOOLEAN NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE UNIQUE INDEX uniq_pathway_setting_code ON pathway_settings (pathway_code)');
        $this->addSql('CREATE TABLE candidate_pathways (id UUID NOT NULL, candidate_id UUID NOT NULL, pathway_template_id UUID NOT NULL, status VARCHAR(255) NOT NULL, blocked_reason TEXT DEFAULT NULL, progress_percent INT NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_candidate_pathways_candidate ON candidate_pathways (candidate_id)');
        $this->addSql('CREATE INDEX IDX_candidate_pathways_template ON candidate_pathways (pathway_template_id)');
        $this->addSql('CREATE UNIQUE INDEX uniq_candidate_pathway ON candidate_pathways (candidate_id, pathway_template_id)');
        $this->addSql('CREATE TABLE candidate_pathway_stages (id UUID NOT NULL, candidate_pathway_id UUID NOT NULL, stage_template_id UUID NOT NULL, sort_order INT NOT NULL, progress_percent INT NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_candidate_pathway_stages_pathway ON candidate_pathway_stages (candidate_pathway_id)');
        $this->addSql('CREATE INDEX IDX_candidate_pathway_stages_template ON candidate_pathway_stages (stage_template_id)');
        $this->addSql('CREATE TABLE candidate_pathway_sub_steps (id UUID NOT NULL, candidate_stage_id UUID NOT NULL, sub_step_template_id UUID NOT NULL, counselor_validated_by_id UUID DEFAULT NULL, admin_validated_by_id UUID DEFAULT NULL, sort_order INT NOT NULL, due_date TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, counselor_validated_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, admin_validated_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_candidate_pathway_sub_steps_stage ON candidate_pathway_sub_steps (candidate_stage_id)');
        $this->addSql('CREATE INDEX IDX_candidate_pathway_sub_steps_template ON candidate_pathway_sub_steps (sub_step_template_id)');
        $this->addSql('CREATE INDEX IDX_candidate_pathway_sub_steps_counselor ON candidate_pathway_sub_steps (counselor_validated_by_id)');
        $this->addSql('CREATE INDEX IDX_candidate_pathway_sub_steps_admin ON candidate_pathway_sub_steps (admin_validated_by_id)');
        $this->addSql('CREATE TABLE pathway_audit_logs (id UUID NOT NULL, candidate_id UUID NOT NULL, candidate_pathway_id UUID DEFAULT NULL, candidate_sub_step_id UUID DEFAULT NULL, performed_by_id UUID DEFAULT NULL, action VARCHAR(80) NOT NULL, payload JSON DEFAULT NULL, occurred_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_pathway_audit_logs_candidate ON pathway_audit_logs (candidate_id)');
        $this->addSql('ALTER TABLE candidates ADD study_application_type VARCHAR(255) DEFAULT NULL');

        $this->addSql('ALTER TABLE pathway_templates ADD CONSTRAINT FK_pathway_templates_campaign FOREIGN KEY (campaign_id) REFERENCES campaigns (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE pathway_stage_templates ADD CONSTRAINT FK_pathway_stage_templates_pathway FOREIGN KEY (pathway_template_id) REFERENCES pathway_templates (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE pathway_sub_step_templates ADD CONSTRAINT FK_pathway_sub_step_templates_stage FOREIGN KEY (stage_template_id) REFERENCES pathway_stage_templates (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE candidate_pathways ADD CONSTRAINT FK_candidate_pathways_candidate FOREIGN KEY (candidate_id) REFERENCES candidates (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE candidate_pathways ADD CONSTRAINT FK_candidate_pathways_template FOREIGN KEY (pathway_template_id) REFERENCES pathway_templates (id) ON DELETE RESTRICT NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE candidate_pathway_stages ADD CONSTRAINT FK_candidate_pathway_stages_pathway FOREIGN KEY (candidate_pathway_id) REFERENCES candidate_pathways (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE candidate_pathway_stages ADD CONSTRAINT FK_candidate_pathway_stages_template FOREIGN KEY (stage_template_id) REFERENCES pathway_stage_templates (id) ON DELETE RESTRICT NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE candidate_pathway_sub_steps ADD CONSTRAINT FK_candidate_pathway_sub_steps_stage FOREIGN KEY (candidate_stage_id) REFERENCES candidate_pathway_stages (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE candidate_pathway_sub_steps ADD CONSTRAINT FK_candidate_pathway_sub_steps_template FOREIGN KEY (sub_step_template_id) REFERENCES pathway_sub_step_templates (id) ON DELETE RESTRICT NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE candidate_pathway_sub_steps ADD CONSTRAINT FK_candidate_pathway_sub_steps_counselor FOREIGN KEY (counselor_validated_by_id) REFERENCES users (id) ON DELETE SET NULL NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE candidate_pathway_sub_steps ADD CONSTRAINT FK_candidate_pathway_sub_steps_admin FOREIGN KEY (admin_validated_by_id) REFERENCES users (id) ON DELETE SET NULL NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE pathway_audit_logs ADD CONSTRAINT FK_pathway_audit_logs_candidate FOREIGN KEY (candidate_id) REFERENCES candidates (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE pathway_audit_logs ADD CONSTRAINT FK_pathway_audit_logs_pathway FOREIGN KEY (candidate_pathway_id) REFERENCES candidate_pathways (id) ON DELETE SET NULL NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE pathway_audit_logs ADD CONSTRAINT FK_pathway_audit_logs_substep FOREIGN KEY (candidate_sub_step_id) REFERENCES candidate_pathway_sub_steps (id) ON DELETE SET NULL NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE pathway_audit_logs ADD CONSTRAINT FK_pathway_audit_logs_user FOREIGN KEY (performed_by_id) REFERENCES users (id) ON DELETE SET NULL NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE pathway_audit_logs DROP CONSTRAINT FK_pathway_audit_logs_user');
        $this->addSql('ALTER TABLE pathway_audit_logs DROP CONSTRAINT FK_pathway_audit_logs_substep');
        $this->addSql('ALTER TABLE pathway_audit_logs DROP CONSTRAINT FK_pathway_audit_logs_pathway');
        $this->addSql('ALTER TABLE pathway_audit_logs DROP CONSTRAINT FK_pathway_audit_logs_candidate');
        $this->addSql('ALTER TABLE candidate_pathway_sub_steps DROP CONSTRAINT FK_candidate_pathway_sub_steps_admin');
        $this->addSql('ALTER TABLE candidate_pathway_sub_steps DROP CONSTRAINT FK_candidate_pathway_sub_steps_counselor');
        $this->addSql('ALTER TABLE candidate_pathway_sub_steps DROP CONSTRAINT FK_candidate_pathway_sub_steps_template');
        $this->addSql('ALTER TABLE candidate_pathway_sub_steps DROP CONSTRAINT FK_candidate_pathway_sub_steps_stage');
        $this->addSql('ALTER TABLE candidate_pathway_stages DROP CONSTRAINT FK_candidate_pathway_stages_template');
        $this->addSql('ALTER TABLE candidate_pathway_stages DROP CONSTRAINT FK_candidate_pathway_stages_pathway');
        $this->addSql('ALTER TABLE candidate_pathways DROP CONSTRAINT FK_candidate_pathways_template');
        $this->addSql('ALTER TABLE candidate_pathways DROP CONSTRAINT FK_candidate_pathways_candidate');
        $this->addSql('ALTER TABLE pathway_sub_step_templates DROP CONSTRAINT FK_pathway_sub_step_templates_stage');
        $this->addSql('ALTER TABLE pathway_stage_templates DROP CONSTRAINT FK_pathway_stage_templates_pathway');
        $this->addSql('ALTER TABLE pathway_templates DROP CONSTRAINT FK_pathway_templates_campaign');
        $this->addSql('DROP TABLE pathway_audit_logs');
        $this->addSql('DROP TABLE candidate_pathway_sub_steps');
        $this->addSql('DROP TABLE candidate_pathway_stages');
        $this->addSql('DROP TABLE candidate_pathways');
        $this->addSql('DROP TABLE pathway_settings');
        $this->addSql('DROP TABLE pathway_sub_step_templates');
        $this->addSql('DROP TABLE pathway_stage_templates');
        $this->addSql('DROP TABLE pathway_templates');
        $this->addSql('DROP TABLE campaigns');
        $this->addSql('ALTER TABLE candidates DROP study_application_type');
    }
}
