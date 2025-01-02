<?php

declare(strict_types=1);

namespace Database\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20241224113441 extends AbstractMigration
{

    public function up(Schema $schema): void
    {
        $sql = <<<SQL
            ALTER TABLE users ADD COLUMN profile_picture VARCHAR(200);
        SQL;

        $this->addSql($sql);
    }

    public function down(Schema $schema): void
    {
        $sql = <<<SQL
            ALTER TABLE users DROP COLUMN profile_picture;
        SQL;

        $this->addSql($sql);
    }
}
