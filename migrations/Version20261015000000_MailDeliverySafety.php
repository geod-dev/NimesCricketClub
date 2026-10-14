<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20261015000000_MailDeliverySafety extends AbstractMigration
{
    public function isTransactional(): bool
    {
        return false;
    }

    public function getDescription(): string
    {
        return 'Track payment confirmations and retain subscribers when previously sent content is deleted.';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE customer_form_entry ADD payment_confirmation_sent_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\'');
        $this->addSql('UPDATE customer_form_entry SET payment_confirmation_sent_at = updated_at WHERE is_paid = 1');
        $this->addSql('ALTER TABLE newsletter_subscriber DROP FOREIGN KEY FK_401562C31D980F56, DROP FOREIGN KEY FK_401562C3924562BC');
        $this->addSql('ALTER TABLE newsletter_subscriber ADD CONSTRAINT FK_401562C31D980F56 FOREIGN KEY (last_news_sent_id) REFERENCES news (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE newsletter_subscriber ADD CONSTRAINT FK_401562C3924562BC FOREIGN KEY (last_email_sent_id) REFERENCES newsletter_email (id) ON DELETE SET NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE customer_form_entry DROP payment_confirmation_sent_at');
        $this->addSql('ALTER TABLE newsletter_subscriber DROP FOREIGN KEY FK_401562C31D980F56, DROP FOREIGN KEY FK_401562C3924562BC');
        $this->addSql('ALTER TABLE newsletter_subscriber ADD CONSTRAINT FK_401562C31D980F56 FOREIGN KEY (last_news_sent_id) REFERENCES news (id)');
        $this->addSql('ALTER TABLE newsletter_subscriber ADD CONSTRAINT FK_401562C3924562BC FOREIGN KEY (last_email_sent_id) REFERENCES newsletter_email (id)');
    }
}
