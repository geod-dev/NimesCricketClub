<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20261014212838_CalendarEvent extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create calendar events with timestamps and an optional related news article.';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE calendar_event (id INT AUTO_INCREMENT NOT NULL, related_news_id INT DEFAULT NULL, start_at DATETIME NOT NULL, end_at DATETIME NOT NULL, title VARCHAR(255) NOT NULL, description LONGTEXT NOT NULL, color VARCHAR(255) NOT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', updated_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', INDEX IDX_57FA09C98ABD9305 (related_news_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE calendar_event ADD CONSTRAINT FK_57FA09C98ABD9305 FOREIGN KEY (related_news_id) REFERENCES news (id) ON DELETE SET NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE calendar_event');
    }
}
