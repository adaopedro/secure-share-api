<?php

    declare(strict_types=1);

    namespace Database\Migrations;

    use Doctrine\DBAL\Schema\Schema;
    use Doctrine\Migrations\AbstractMigration;

    /**
     * Auto-generated Migration: Please modify to your needs!
     */
    final class Version20241216220717 extends AbstractMigration {

        public function up(Schema $schema): void {
            $sql = <<<SQL
                CREATE TABLE users (
                    id INT UNSIGNED AUTO_INCREMENT NOT NULL,
                    first_name VARCHAR (60) NOT NULL,
                    last_name VARCHAR (60) NOT NULL,
                    nipId VARCHAR (20) NOT NULL,
                    contact VARCHAR (50) NOT NULL,
                    email VARCHAR (200) NOT NULL,
                    password VARCHAR (200) NOT NULL,
                    is_admin TINYINT(1) NOT NULL DEFAULT 0,
                    PRIMARY KEY(id)
                )
                SQL;
                
            $this->addSql($sql);
        }

        public function down(Schema $schema): void {
            $this->addSql("DROP TABLE users");            
        }
    }
