<?php

declare(strict_types=1);

namespace Database\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20241223153544 extends AbstractMigration {

    public function up(Schema $schema): void {
        $sql = <<<SQL
            ALTER TABLE users
                RENAME COLUMN nipId TO nip_id;
        SQL;

        $this->addSql($sql);

    }

    public function down(Schema $schema): void
    {
        $sql = <<<SQL
            ALTER TABLE users
                RENAME COLUMN nip_id TO nipId;
        SQL;

        $this->addSql($sql);

    }
}
