<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260115155244_NewsletterSubscriberData extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add last_contacted_at, last_email_sent, and last_news_sent fields to newsletter_subscriber table.';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql(
            'ALTER TABLE newsletter_subscriber ADD last_contacted_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', ADD last_email_sent_id BINARY(16) DEFAULT NULL, ADD last_news_sent_id INT DEFAULT NULL'
        );
        $this->addSql('ALTER TABLE newsletter_subscriber ADD CONSTRAINT FK_401562C31D980F56 FOREIGN KEY (last_news_sent_id) REFERENCES news (id)');
        $this->addSql('ALTER TABLE newsletter_subscriber ADD CONSTRAINT FK_401562C3924562BC FOREIGN KEY (last_email_sent_id) REFERENCES newsletter_email (id)');
        $this->addSql('CREATE INDEX IDX_401562C31D980F56 ON newsletter_subscriber (last_news_sent_id)');
        $this->addSql('CREATE INDEX IDX_401562C3924562BC ON newsletter_subscriber (last_email_sent_id)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE newsletter_subscriber DROP FOREIGN KEY FK_401562C31D980F56');
        $this->addSql('ALTER TABLE newsletter_subscriber DROP FOREIGN KEY FK_401562C3924562BC');
        $this->addSql('DROP INDEX IDX_401562C31D980F56 ON newsletter_subscriber');
        $this->addSql('DROP INDEX IDX_401562C3924562BC ON newsletter_subscriber');
        $this->addSql(
            'ALTER TABLE newsletter_subscriber DROP last_contacted_at, DROP last_email_sent_id, DROP last_news_sent_id'
        );
    }
}
