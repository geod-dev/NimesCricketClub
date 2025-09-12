<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20250912074605_FormsAndNewsletter extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Introduce customer forms (forms, fields, entries), add link associations for news, create newsletter subscribers table, and add newsletter fields to news entities.';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE customer_form (id BINARY(16) NOT NULL COMMENT \'(DC2Type:uuid)\', slug VARCHAR(255) NOT NULL, price VARCHAR(255) NOT NULL, is_repeatable TINYINT(1) NOT NULL, entity_name_fr VARCHAR(255) DEFAULT NULL, entity_name_en VARCHAR(255) DEFAULT NULL, title_fr TINYTEXT NOT NULL, title_en TINYTEXT NOT NULL, content_fr LONGTEXT DEFAULT NULL, content_en LONGTEXT DEFAULT NULL, translation_version_hash LONGTEXT DEFAULT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', updated_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE customer_form_entry (id BINARY(16) NOT NULL COMMENT \'(DC2Type:uuid)\', form_id BINARY(16) NOT NULL COMMENT \'(DC2Type:uuid)\', data JSON NOT NULL COMMENT \'(DC2Type:json)\', is_paid TINYINT(1) NOT NULL, price DOUBLE PRECISION NOT NULL, email VARCHAR(255) NOT NULL, first_name VARCHAR(255) NOT NULL, last_name VARCHAR(255) NOT NULL, phone VARCHAR(255) NOT NULL, checkout_session_id VARCHAR(255) DEFAULT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', updated_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', INDEX IDX_55AF3D315FF69B7D (form_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE customer_form_field (id BINARY(16) NOT NULL COMMENT \'(DC2Type:uuid)\', form_id BINARY(16) NOT NULL COMMENT \'(DC2Type:uuid)\', name_fr VARCHAR(255) NOT NULL, name_en VARCHAR(255) NOT NULL, type VARCHAR(255) NOT NULL, is_required TINYINT(1) NOT NULL, reference VARCHAR(255) NOT NULL, INDEX IDX_257BE5195FF69B7D (form_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');

        $this->addSql('CREATE TABLE link (id INT AUTO_INCREMENT NOT NULL, title VARCHAR(255) NOT NULL, url VARCHAR(255) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE news_link (news_id INT NOT NULL, link_id INT NOT NULL, INDEX IDX_E3716F7AB5A459A0 (news_id), INDEX IDX_E3716F7AADA40271 (link_id), PRIMARY KEY(news_id, link_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');

        $this->addSql('CREATE TABLE newsletter_subscriber (id BINARY(16) NOT NULL COMMENT \'(DC2Type:uuid)\', email VARCHAR(255) NOT NULL, unsubscribe_token VARCHAR(255) NOT NULL, is_subscribed TINYINT(1) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');

        $this->addSql('ALTER TABLE customer_form_entry ADD CONSTRAINT FK_55AF3D315FF69B7D FOREIGN KEY (form_id) REFERENCES customer_form (id)');
        $this->addSql('ALTER TABLE customer_form_field ADD CONSTRAINT FK_257BE5195FF69B7D FOREIGN KEY (form_id) REFERENCES customer_form (id)');
        $this->addSql('ALTER TABLE news_link ADD CONSTRAINT FK_E3716F7AB5A459A0 FOREIGN KEY (news_id) REFERENCES news (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE news_link ADD CONSTRAINT FK_E3716F7AADA40271 FOREIGN KEY (link_id) REFERENCES link (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE news ADD is_published TINYINT(1) NOT NULL, ADD published_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\'');

        $this->addSql("UPDATE news SET published_at = '1970-01-01 00:00:00'");
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE customer_form_entry DROP FOREIGN KEY FK_55AF3D315FF69B7D');
        $this->addSql('ALTER TABLE customer_form_field DROP FOREIGN KEY FK_257BE5195FF69B7D');
        $this->addSql('ALTER TABLE news_link DROP FOREIGN KEY FK_E3716F7AB5A459A0');
        $this->addSql('ALTER TABLE news_link DROP FOREIGN KEY FK_E3716F7AADA40271');

        $this->addSql('DROP TABLE customer_form');
        $this->addSql('DROP TABLE customer_form_entry');
        $this->addSql('DROP TABLE customer_form_field');

        $this->addSql('DROP TABLE link');
        $this->addSql('DROP TABLE news_link');

        $this->addSql('DROP TABLE newsletter_subscriber');

        $this->addSql('ALTER TABLE news DROP is_published, DROP published_at');
    }
}
