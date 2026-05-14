<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260512111746 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE report_category (id INT AUTO_INCREMENT NOT NULL, label VARCHAR(255) NOT NULL, icon VARCHAR(255) DEFAULT NULL, PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('ALTER TABLE reports ADD category_id INT DEFAULT NULL, CHANGE reason description VARCHAR(255) NOT NULL');
        $this->addSql('ALTER TABLE reports ADD CONSTRAINT FK_F11FA74512469DE2 FOREIGN KEY (category_id) REFERENCES report_category (id)');
        $this->addSql('CREATE INDEX IDX_F11FA74512469DE2 ON reports (category_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE report_category');
        $this->addSql('ALTER TABLE reports DROP FOREIGN KEY FK_F11FA74512469DE2');
        $this->addSql('DROP INDEX IDX_F11FA74512469DE2 ON reports');
        $this->addSql('ALTER TABLE reports DROP category_id, CHANGE description reason VARCHAR(255) NOT NULL');
    }
}
