<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250817175701 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE "user" (id UUID NOT NULL, email VARCHAR(180) NOT NULL, password VARCHAR(255) DEFAULT NULL, username VARCHAR(255) NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_8D93D649E7927C74 ON "user" (email)');
        $this->addSql('COMMENT ON COLUMN "user".id IS \'(DC2Type:uuid)\'');
        $this->addSql('CREATE TABLE addiction (id UUID NOT NULL, user_id UUID NOT NULL, name VARCHAR(100) NOT NULL, quantity INT NOT NULL, total_amount DOUBLE PRECISION NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_9C14E8A2A76ED395 ON addiction (user_id)');
        $this->addSql('COMMENT ON COLUMN addiction.id IS \'(DC2Type:uuid)\'');
        $this->addSql('COMMENT ON COLUMN addiction.user_id IS \'(DC2Type:uuid)\'');
        $this->addSql('CREATE TABLE trigger (id UUID NOT NULL, type VARCHAR(255) NOT NULL, created_at TIMESTAMP(0) WITH TIME ZONE DEFAULT NULL, updated_at TIMESTAMP(0) WITH TIME ZONE DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('COMMENT ON COLUMN trigger.id IS \'(DC2Type:uuid)\'');
        $this->addSql('CREATE TABLE trigger_addictions (trigger_id UUID NOT NULL, addiction_id UUID NOT NULL, PRIMARY KEY(trigger_id, addiction_id))');
        $this->addSql('CREATE INDEX IDX_316D106B5FDDDCD6 ON trigger_addictions (trigger_id)');
        $this->addSql('CREATE INDEX IDX_316D106B30C0E13B ON trigger_addictions (addiction_id)');
        $this->addSql('COMMENT ON COLUMN trigger_addictions.trigger_id IS \'(DC2Type:uuid)\'');
        $this->addSql('COMMENT ON COLUMN trigger_addictions.addiction_id IS \'(DC2Type:uuid)\'');
        $this->addSql('ALTER TABLE addiction ADD CONSTRAINT FK_9C14E8A2A76ED395 FOREIGN KEY (user_id) REFERENCES "user" (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE trigger_addictions ADD CONSTRAINT FK_316D106B5FDDDCD6 FOREIGN KEY (trigger_id) REFERENCES trigger (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE trigger_addictions ADD CONSTRAINT FK_316D106B30C0E13B FOREIGN KEY (addiction_id) REFERENCES addiction (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE addiction DROP CONSTRAINT FK_9C14E8A2A76ED395');
        $this->addSql('ALTER TABLE trigger_addictions DROP CONSTRAINT FK_316D106B5FDDDCD6');
        $this->addSql('ALTER TABLE trigger_addictions DROP CONSTRAINT FK_316D106B30C0E13B');
        $this->addSql('DROP TABLE "user"');
        $this->addSql('DROP TABLE addiction');
        $this->addSql('DROP TABLE trigger');
        $this->addSql('DROP TABLE trigger_addictions');
    }
}
