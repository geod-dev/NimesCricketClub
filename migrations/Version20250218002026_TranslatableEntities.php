<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250218002026_TranslatableEntities extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add english version of content/description field of activity, competition and news entities.';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('
            ALTER TABLE activity
            CHANGE name title_fr TINYTEXT NOT NULL,
            ADD title_en TINYTEXT NOT NULL,
            CHANGE content content_fr LONGTEXT DEFAULT NULL,
            ADD content_en LONGTEXT DEFAULT NULL,
            ADD translation_version_hash LONGTEXT DEFAULT NULL
        ');
        $this->addSql('
            ALTER TABLE competition
            CHANGE name title_fr TINYTEXT NOT NULL,
            ADD title_en TINYTEXT NOT NULL,
            CHANGE description content_fr LONGTEXT DEFAULT NULL,
            ADD content_en LONGTEXT DEFAULT NULL,
            ADD translation_version_hash LONGTEXT DEFAULT NULL
        ');
        $this->addSql('
            ALTER TABLE news
            CHANGE title title_fr TINYTEXT NOT NULL,
            ADD title_en TINYTEXT NOT NULL,
            CHANGE content content_fr LONGTEXT DEFAULT NULL,
            ADD content_en LONGTEXT DEFAULT NULL,
            ADD translation_version_hash LONGTEXT DEFAULT NULL
        ');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('
            ALTER TABLE activity
            CHANGE title_fr name TINYTEXT NOT NULL,
            DROP title_en,
            CHANGE content_fr content LONGTEXT NOT NULL,
            DROP content_en,
            DROP translation_version_hash
        ');
        $this->addSql('
            ALTER TABLE competition
            CHANGE title_fr name TINYTEXT NOT NULL,
            DROP title_en,
            CHANGE content_fr description LONGTEXT DEFAULT NULL,
            DROP content_en,
            DROP translation_version_hash
        ');
        $this->addSql('
            ALTER TABLE news
            CHANGE title_fr title TINYTEXT NOT NULL,
            DROP title_en,
            CHANGE content_fr content LONGTEXT NOT NULL,
            DROP content_en,
            DROP translation_version_hash
        ');
    }
}
