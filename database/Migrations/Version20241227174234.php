<?php

declare(strict_types=1);

namespace Database\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20241227174234 extends AbstractMigration
{

    public function up(Schema $schema): void
    {
        $sql = <<<SQL
            ALTER TABLE posts MODIFY COLUMN cover_photo VARCHAR(200) DEFAULT NULL;
            ALTER TABLE posts ADD COLUMN views INT NOT NULL DEFAULT 0;
            ALTER TABLE posts DROP INDEX title;
            ALTER TABLE post_attachments MODIFY COLUMN file VARCHAR(200) DEFAULT NULL;
        SQL;

        $this->addSql($sql);
    }
    
    public function down(Schema $schema): void
    {
        $sql = <<<SQL
            ALTER TABLE posts MODIFY COLUMN cover_photo VARCHAR(200) NOT NULL;
            ALTER TABLE posts DROP COLUMN views;
            ALTER TABLE posts ADD UNIQUE(title);
            ALTER TABLE post_attachments MODIFY COLUMN file VARCHAR(200) NOT NULL;
        SQL;
        
        $this->addSql($sql);
    }
}
