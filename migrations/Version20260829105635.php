<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260829105635 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE gallery DROP FOREIGN KEY `FK_472B783A71F7E88B`');
        $this->addSql('ALTER TABLE gallery CHANGE event_id event_id INT NOT NULL');
        $this->addSql('ALTER TABLE gallery ADD CONSTRAINT FK_472B783A71F7E88B FOREIGN KEY (event_id) REFERENCES event (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE ticket_type DROP FOREIGN KEY `FK_BE05421171F7E88B`');
        $this->addSql('ALTER TABLE ticket_type CHANGE event_id event_id INT NOT NULL');
        $this->addSql('ALTER TABLE ticket_type ADD CONSTRAINT FK_BE05421171F7E88B FOREIGN KEY (event_id) REFERENCES event (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE gallery DROP FOREIGN KEY FK_472B783A71F7E88B');
        $this->addSql('ALTER TABLE gallery CHANGE event_id event_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE gallery ADD CONSTRAINT `FK_472B783A71F7E88B` FOREIGN KEY (event_id) REFERENCES event (id)');
        $this->addSql('ALTER TABLE ticket_type DROP FOREIGN KEY FK_BE05421171F7E88B');
        $this->addSql('ALTER TABLE ticket_type CHANGE event_id event_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE ticket_type ADD CONSTRAINT `FK_BE05421171F7E88B` FOREIGN KEY (event_id) REFERENCES event (id)');
    }
}
