<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250819142949 extends AbstractMigration
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
        $this->addSql('CREATE TABLE addiction (id UUID NOT NULL, user_id UUID NOT NULL, type VARCHAR(100) NOT NULL, total_amount DOUBLE PRECISION NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_9C14E8A2A76ED395 ON addiction (user_id)');
        $this->addSql('COMMENT ON COLUMN addiction.id IS \'(DC2Type:uuid)\'');
        $this->addSql('COMMENT ON COLUMN addiction.user_id IS \'(DC2Type:uuid)\'');
        $this->addSql('ALTER TABLE addiction ADD CONSTRAINT FK_9C14E8A2A76ED395 FOREIGN KEY (user_id) REFERENCES "user" (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE addiction DROP CONSTRAINT FK_9C14E8A2A76ED395');
        $this->addSql('DROP TABLE "user"');
        $this->addSql('DROP TABLE addiction');
    }
}
