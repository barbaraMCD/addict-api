<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250819180008 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE "trigger" (id UUID NOT NULL, type VARCHAR(100) NOT NULL, PRIMARY KEY(id))');
        $this->addSql('COMMENT ON COLUMN "trigger".id IS \'(DC2Type:uuid)\'');
        $this->addSql('CREATE TABLE trigger_consumption (trigger_id UUID NOT NULL, consumption_id UUID NOT NULL, PRIMARY KEY(trigger_id, consumption_id))');
        $this->addSql('CREATE INDEX IDX_5387074B5FDDDCD6 ON trigger_consumption (trigger_id)');
        $this->addSql('CREATE INDEX IDX_5387074BD17C3821 ON trigger_consumption (consumption_id)');
        $this->addSql('COMMENT ON COLUMN trigger_consumption.trigger_id IS \'(DC2Type:uuid)\'');
        $this->addSql('COMMENT ON COLUMN trigger_consumption.consumption_id IS \'(DC2Type:uuid)\'');
        $this->addSql('CREATE TABLE "user" (id UUID NOT NULL, email VARCHAR(180) NOT NULL, password VARCHAR(255) DEFAULT NULL, username VARCHAR(255) NOT NULL, created_at TIMESTAMP(0) WITH TIME ZONE DEFAULT NULL, updated_at TIMESTAMP(0) WITH TIME ZONE DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_8D93D649E7927C74 ON "user" (email)');
        $this->addSql('COMMENT ON COLUMN "user".id IS \'(DC2Type:uuid)\'');
        $this->addSql('CREATE TABLE addiction (id UUID NOT NULL, user_id UUID NOT NULL, type VARCHAR(100) NOT NULL, status VARCHAR(100) NOT NULL, total_amount DOUBLE PRECISION NOT NULL, created_at TIMESTAMP(0) WITH TIME ZONE DEFAULT NULL, updated_at TIMESTAMP(0) WITH TIME ZONE DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_9C14E8A2A76ED395 ON addiction (user_id)');
        $this->addSql('COMMENT ON COLUMN addiction.id IS \'(DC2Type:uuid)\'');
        $this->addSql('COMMENT ON COLUMN addiction.user_id IS \'(DC2Type:uuid)\'');
        $this->addSql('CREATE TABLE consumption (id UUID NOT NULL, addiction_id UUID NOT NULL, quantity INT NOT NULL, comment VARCHAR(255) DEFAULT NULL, date TIMESTAMP(0) WITH TIME ZONE NOT NULL, created_at TIMESTAMP(0) WITH TIME ZONE DEFAULT NULL, updated_at TIMESTAMP(0) WITH TIME ZONE DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_2CFF2DF930C0E13B ON consumption (addiction_id)');
        $this->addSql('COMMENT ON COLUMN consumption.id IS \'(DC2Type:uuid)\'');
        $this->addSql('COMMENT ON COLUMN consumption.addiction_id IS \'(DC2Type:uuid)\'');
        $this->addSql('ALTER TABLE trigger_consumption ADD CONSTRAINT FK_5387074B5FDDDCD6 FOREIGN KEY (trigger_id) REFERENCES "trigger" (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE trigger_consumption ADD CONSTRAINT FK_5387074BD17C3821 FOREIGN KEY (consumption_id) REFERENCES consumption (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE addiction ADD CONSTRAINT FK_9C14E8A2A76ED395 FOREIGN KEY (user_id) REFERENCES "user" (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE consumption ADD CONSTRAINT FK_2CFF2DF930C0E13B FOREIGN KEY (addiction_id) REFERENCES addiction (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE trigger_consumption DROP CONSTRAINT FK_5387074B5FDDDCD6');
        $this->addSql('ALTER TABLE trigger_consumption DROP CONSTRAINT FK_5387074BD17C3821');
        $this->addSql('ALTER TABLE addiction DROP CONSTRAINT FK_9C14E8A2A76ED395');
        $this->addSql('ALTER TABLE consumption DROP CONSTRAINT FK_2CFF2DF930C0E13B');
        $this->addSql('DROP TABLE "trigger"');
        $this->addSql('DROP TABLE trigger_consumption');
        $this->addSql('DROP TABLE "user"');
        $this->addSql('DROP TABLE addiction');
        $this->addSql('DROP TABLE consumption');
    }
}
