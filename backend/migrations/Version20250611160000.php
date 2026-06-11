<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20250611160000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Sprint 1 — Auth, RBAC, Audit Trail, MFA';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE permissions (id UUID NOT NULL, code VARCHAR(100) NOT NULL, name VARCHAR(150) NOT NULL, description TEXT DEFAULT NULL, is_system BOOLEAN NOT NULL, PRIMARY KEY (id))');
        $this->addSql('CREATE UNIQUE INDEX uniq_permission_code ON permissions (code)');

        $this->addSql('CREATE TABLE roles (id UUID NOT NULL, code VARCHAR(50) NOT NULL, name VARCHAR(100) NOT NULL, description TEXT DEFAULT NULL, is_system BOOLEAN NOT NULL, PRIMARY KEY (id))');
        $this->addSql('CREATE UNIQUE INDEX uniq_role_code ON roles (code)');

        $this->addSql('CREATE TABLE users (id UUID NOT NULL, email VARCHAR(180) NOT NULL, password VARCHAR(255) NOT NULL, first_name VARCHAR(100) NOT NULL, last_name VARCHAR(100) NOT NULL, is_active BOOLEAN NOT NULL, mfa_enabled BOOLEAN NOT NULL, mfa_secret VARCHAR(255) DEFAULT NULL, locked_until TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, failed_login_attempts INT NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, password_changed_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, PRIMARY KEY (id))');
        $this->addSql('CREATE UNIQUE INDEX uniq_user_email ON users (email)');

        $this->addSql('CREATE TABLE role_permissions (role_id UUID NOT NULL, permission_id UUID NOT NULL, PRIMARY KEY (role_id, permission_id))');
        $this->addSql('CREATE INDEX IDX_7C8E6C5BD60322AC ON role_permissions (role_id)');
        $this->addSql('CREATE INDEX IDX_7C8E6C5BFED90CCA ON role_permissions (permission_id)');
        $this->addSql('ALTER TABLE role_permissions ADD CONSTRAINT FK_7C8E6C5BD60322AC FOREIGN KEY (role_id) REFERENCES roles (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE role_permissions ADD CONSTRAINT FK_7C8E6C5BFED90CCA FOREIGN KEY (permission_id) REFERENCES permissions (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');

        $this->addSql('CREATE TABLE user_roles (user_id UUID NOT NULL, role_id UUID NOT NULL, PRIMARY KEY (user_id, role_id))');
        $this->addSql('CREATE INDEX IDX_54FCD59FA76ED395 ON user_roles (user_id)');
        $this->addSql('CREATE INDEX IDX_54FCD59FD60322AC ON user_roles (role_id)');
        $this->addSql('ALTER TABLE user_roles ADD CONSTRAINT FK_54FCD59FA76ED395 FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE user_roles ADD CONSTRAINT FK_54FCD59FD60322AC FOREIGN KEY (role_id) REFERENCES roles (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');

        $this->addSql('CREATE TABLE audit_trails (id UUID NOT NULL, user_id UUID DEFAULT NULL, action VARCHAR(100) NOT NULL, entity_type VARCHAR(100) NOT NULL, entity_id VARCHAR(36) DEFAULT NULL, old_value JSON DEFAULT NULL, new_value JSON DEFAULT NULL, ip_address VARCHAR(45) DEFAULT NULL, user_agent VARCHAR(500) DEFAULT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, PRIMARY KEY (id))');
        $this->addSql('CREATE INDEX IDX_787CA4E9A76ED395 ON audit_trails (user_id)');
        $this->addSql('ALTER TABLE audit_trails ADD CONSTRAINT FK_787CA4E9A76ED395 FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE SET NULL NOT DEFERRABLE INITIALLY IMMEDIATE');

        $this->addSql('CREATE TABLE security_logs (id UUID NOT NULL, user_id UUID DEFAULT NULL, event VARCHAR(50) NOT NULL, email VARCHAR(180) DEFAULT NULL, ip_address VARCHAR(45) DEFAULT NULL, user_agent VARCHAR(500) DEFAULT NULL, metadata JSON DEFAULT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, PRIMARY KEY (id))');
        $this->addSql('CREATE INDEX IDX_8170D314A76ED395 ON security_logs (user_id)');
        $this->addSql('ALTER TABLE security_logs ADD CONSTRAINT FK_8170D314A76ED395 FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE SET NULL NOT DEFERRABLE INITIALLY IMMEDIATE');

        $this->addSql('CREATE TABLE password_reset_tokens (id UUID NOT NULL, user_id UUID NOT NULL, token_hash VARCHAR(64) NOT NULL, expires_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, used_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, PRIMARY KEY (id))');
        $this->addSql('CREATE INDEX IDX_396EF8DCA76ED395 ON password_reset_tokens (user_id)');
        $this->addSql('ALTER TABLE password_reset_tokens ADD CONSTRAINT FK_396EF8DCA76ED395 FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');

        $this->addSql('CREATE TABLE mfa_recovery_codes (id UUID NOT NULL, user_id UUID NOT NULL, code_hash VARCHAR(64) NOT NULL, used_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, PRIMARY KEY (id))');
        $this->addSql('CREATE INDEX IDX_7F0B8D0DA76ED395 ON mfa_recovery_codes (user_id)');
        $this->addSql('ALTER TABLE mfa_recovery_codes ADD CONSTRAINT FK_7F0B8D0DA76ED395 FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');

        $this->addSql('CREATE TABLE refresh_tokens (id SERIAL NOT NULL, refresh_token VARCHAR(128) NOT NULL, username VARCHAR(255) NOT NULL, valid TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, PRIMARY KEY (id))');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_9BACE7E1C74F2195 ON refresh_tokens (refresh_token)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE audit_trails DROP CONSTRAINT FK_787CA4E9A76ED395');
        $this->addSql('ALTER TABLE security_logs DROP CONSTRAINT FK_8170D314A76ED395');
        $this->addSql('ALTER TABLE password_reset_tokens DROP CONSTRAINT FK_396EF8DCA76ED395');
        $this->addSql('ALTER TABLE mfa_recovery_codes DROP CONSTRAINT FK_7F0B8D0DA76ED395');
        $this->addSql('ALTER TABLE user_roles DROP CONSTRAINT FK_54FCD59FA76ED395');
        $this->addSql('ALTER TABLE user_roles DROP CONSTRAINT FK_54FCD59FD60322AC');
        $this->addSql('ALTER TABLE role_permissions DROP CONSTRAINT FK_7C8E6C5BD60322AC');
        $this->addSql('ALTER TABLE role_permissions DROP CONSTRAINT FK_7C8E6C5BFED90CCA');
        $this->addSql('DROP TABLE refresh_tokens');
        $this->addSql('DROP TABLE mfa_recovery_codes');
        $this->addSql('DROP TABLE password_reset_tokens');
        $this->addSql('DROP TABLE security_logs');
        $this->addSql('DROP TABLE audit_trails');
        $this->addSql('DROP TABLE user_roles');
        $this->addSql('DROP TABLE role_permissions');
        $this->addSql('DROP TABLE users');
        $this->addSql('DROP TABLE roles');
        $this->addSql('DROP TABLE permissions');
    }
}
