<?php

declare(strict_types=1);

namespace Database\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20241227043608 extends AbstractMigration
{

    public function up(Schema $schema): void
    {
        $sql = <<<SQL
            CREATE TABLE posts (
                id INT UNSIGNED NOT NULL AUTO_INCREMENT,
                title VARCHAR(100) NOT NULL,
                description TEXT NOT NULL,
                post_type ENUM("Denúncia", "Notícia") NOT NULL DEFAULT "Denúncia",
                cover_photo VARCHAR(200) NOT NULL,
                created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
                user_id INT UNSIGNED NOT NULL,
                PRIMARY KEY(id),
                UNIQUE(title),
                CONSTRAINT fk_user_id
                    FOREIGN KEY (user_id)
                    REFERENCES users (id)
                    ON DELETE CASCADE
                    ON UPDATE CASCADE
            );

            CREATE TABLE post_attachments (
                id INT UNSIGNED NOT NULL AUTO_INCREMENT,
                name VARCHAR(100) NOT NULL,
                file VARCHAR(200) NOT NULL,
                created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
                post_id INT UNSIGNED NOT NULL,
                PRIMARY KEY(id),
                CONSTRAINT fk_post_id
                    FOREIGN KEY (post_id)
                    REFERENCES posts (id)
                    ON DELETE CASCADE
                    ON UPDATE CASCADE
            );

            
        SQL;

        $this->addSql($sql);

    }

    public function down(Schema $schema): void
    {
        $sql = <<<SQL
            DROP TABLE post_attachments;
            DROP TABLE posts;
        SQL;

        $this->addSql($sql);
    }
}
