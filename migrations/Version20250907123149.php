<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250907123149 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE subscription (id UUID NOT NULL, user_id UUID NOT NULL, stripe_subscription_id VARCHAR(255) NOT NULL, stripe_customer_id VARCHAR(255) NOT NULL, current_period_start TIMESTAMP(0) WITH TIME ZONE NOT NULL, current_period_end TIMESTAMP(0) WITH TIME ZONE NOT NULL, plan_type VARCHAR(50) NOT NULL, created_at TIMESTAMP(0) WITH TIME ZONE DEFAULT NULL, updated_at TIMESTAMP(0) WITH TIME ZONE DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_A3C664D3B5DBB761 ON subscription (stripe_subscription_id)');
        $this->addSql('CREATE INDEX IDX_A3C664D3A76ED395 ON subscription (user_id)');
        $this->addSql('COMMENT ON COLUMN subscription.id IS \'(DC2Type:uuid)\'');
        $this->addSql('COMMENT ON COLUMN subscription.user_id IS \'(DC2Type:uuid)\'');
        $this->addSql('COMMENT ON COLUMN subscription.current_period_start IS \'(DC2Type:datetimetz_immutable)\'');
        $this->addSql('COMMENT ON COLUMN subscription.current_period_end IS \'(DC2Type:datetimetz_immutable)\'');
        $this->addSql('ALTER TABLE subscription ADD CONSTRAINT FK_A3C664D3A76ED395 FOREIGN KEY (user_id) REFERENCES "user" (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE "user" ALTER email DROP NOT NULL');
        $this->addSql('ALTER TABLE "user" ALTER password DROP NOT NULL');
        $this->addSql('ALTER TABLE addiction ALTER status SET DEFAULT \'active\'');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE subscription DROP CONSTRAINT FK_A3C664D3A76ED395');
        $this->addSql('DROP TABLE subscription');
        $this->addSql('ALTER TABLE "user" ALTER email SET NOT NULL');
        $this->addSql('ALTER TABLE "user" ALTER password SET NOT NULL');
        $this->addSql('ALTER TABLE addiction ALTER status SET DEFAULT \'Active\'');
    }
}
