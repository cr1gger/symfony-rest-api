<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20211117145901 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE brand ADD CONSTRAINT FK_1C52F95819EB6921 FOREIGN KEY (client_id) REFERENCES client (id)');
        $this->addSql('CREATE INDEX IDX_1C52F95819EB6921 ON brand (client_id)');
        $this->addSql('ALTER TABLE client CHANGE is_active is_active INT DEFAULT 0 NOT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE brand DROP FOREIGN KEY FK_1C52F95819EB6921');
        $this->addSql('DROP INDEX IDX_1C52F95819EB6921 ON brand');
        $this->addSql('ALTER TABLE client CHANGE is_active is_active INT NOT NULL');
    }
}
