<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * The legacy CodeIgniter schema — the 24 tables this app inherited.
 *
 * Until this migration existed the schema lived only in a SQL dump, so
 * `migrate` against an empty database built nothing: every additive migration
 * after this one guards on `Schema::hasTable()` and so silently did nothing,
 * and `create_labour_category_table` then died seeding itself from a `labour`
 * table that nobody had created. This is the missing first step, and what
 * makes `migrate` on an empty database produce a working app.
 *
 * The statements are real `SHOW CREATE TABLE` output rather than Blueprint
 * calls, because the Blueprint API cannot express this schema faithfully:
 *
 *   - the charset is per-table, not per-database: 14 tables are latin1 and 10
 *     are utf8mb4, which decides how their strings sort and compare
 *   - column names are inconsistent by inheritance (`admin`.`Name` is the one
 *     capitalised column) and have to survive verbatim
 *   - several columns are NOT NULL with no default, which Blueprint models
 *     awkwardly but a raw statement states plainly
 *
 * Integer display widths are deliberately dropped. The legacy schema declared
 * `int(22)` and `int(200)`, but width never constrained anything — an INT is
 * four bytes whatever the number in brackets — and MySQL 8 removed the
 * feature, so production's own mysqldump now emits plain `int`. Emitting the
 * old widths here would make a migrated database disagree with one restored
 * from the production dump, which is the comparison that actually matters.
 *
 * `Company_architect_site` keeps its capital C: that is how production spells
 * it and what App\Models\CompanyArchitectSite asks for. A dump taken on
 * Windows folds it to lowercase, which then fails to resolve on the
 * case-sensitive Linux server.
 *
 * AUTO_INCREMENT seeds are stripped so a fresh database starts at 1, while an
 * imported dump keeps whatever its own ALTER statements set.
 *
 * Each table is created only if absent, so this is safe to run against a
 * database that was populated from a dump — which is what production is.
 */
return new class extends Migration
{
    /** table => its exact CREATE TABLE statement */
    private function tables(): array
    {
        return [
            'admin' => <<<'SQL'
                CREATE TABLE `admin` (
                  `id` int NOT NULL AUTO_INCREMENT,
                  `Name` varchar(200) NOT NULL,
                  `username` varchar(200) NOT NULL,
                  `email` varchar(200) NOT NULL,
                  `password` varchar(200) NOT NULL,
                  PRIMARY KEY (`id`)
                ) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci
                SQL,

            'architect_detail' => <<<'SQL'
                CREATE TABLE `architect_detail` (
                  `id` int NOT NULL AUTO_INCREMENT,
                  `payment` int NOT NULL,
                  `source` varchar(200) NOT NULL,
                  `date` varchar(200) NOT NULL,
                  `proj_id` int NOT NULL,
                  PRIMARY KEY (`id`)
                ) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci
                SQL,

            'architect_detail_company' => <<<'SQL'
                CREATE TABLE `architect_detail_company` (
                  `id` int NOT NULL AUTO_INCREMENT,
                  `payment` varchar(200) NOT NULL,
                  `source` varchar(200) NOT NULL,
                  `date` varchar(200) NOT NULL,
                  `proj_id` varchar(200) NOT NULL,
                  PRIMARY KEY (`id`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
                SQL,

            'architect_site' => <<<'SQL'
                CREATE TABLE `architect_site` (
                  `id` int NOT NULL AUTO_INCREMENT,
                  `name` varchar(200) NOT NULL,
                  `phase` varchar(200) NOT NULL,
                  `sector` varchar(200) NOT NULL,
                  `total_price` int NOT NULL,
                  PRIMARY KEY (`id`)
                ) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci
                SQL,

            'b_category' => <<<'SQL'
                CREATE TABLE `b_category` (
                  `id` int NOT NULL AUTO_INCREMENT,
                  `material_name` varchar(200) NOT NULL,
                  PRIMARY KEY (`id`)
                ) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci
                SQL,

            'b_material' => <<<'SQL'
                CREATE TABLE `b_material` (
                  `id` int NOT NULL AUTO_INCREMENT,
                  `login_user` varchar(200) NOT NULL,
                  `detail` varchar(200) NOT NULL,
                  `quantity` varchar(200) NOT NULL,
                  `price` varchar(200) NOT NULL,
                  `project_id` varchar(200) NOT NULL,
                  `proj_name` varchar(200) NOT NULL,
                  `type` varchar(200) NOT NULL,
                  `status` int NOT NULL,
                  `date` varchar(200) NOT NULL,
                  PRIMARY KEY (`id`)
                ) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci
                SQL,

            'category' => <<<'SQL'
                CREATE TABLE `category` (
                  `id` int NOT NULL AUTO_INCREMENT,
                  `material_name` varchar(200) NOT NULL,
                  PRIMARY KEY (`id`)
                ) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci
                SQL,

            'Company_architect_site' => <<<'SQL'
                CREATE TABLE `Company_architect_site` (
                  `id` int NOT NULL AUTO_INCREMENT,
                  `name1` varchar(200) NOT NULL,
                  `phase1` varchar(200) NOT NULL,
                  `sector1` varchar(200) NOT NULL,
                  `total_price1` varchar(200) NOT NULL,
                  PRIMARY KEY (`id`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
                SQL,

            'construction_detail' => <<<'SQL'
                CREATE TABLE `construction_detail` (
                  `id` int NOT NULL AUTO_INCREMENT,
                  `payment` int NOT NULL,
                  `source` varchar(200) NOT NULL,
                  `date` varchar(200) NOT NULL,
                  `proj_id` int NOT NULL,
                  PRIMARY KEY (`id`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
                SQL,

            'construction_site' => <<<'SQL'
                CREATE TABLE `construction_site` (
                  `id` int NOT NULL AUTO_INCREMENT,
                  `name` varchar(200) NOT NULL,
                  `phase` varchar(200) NOT NULL,
                  `sector` varchar(200) NOT NULL,
                  `total_price` int NOT NULL,
                  PRIMARY KEY (`id`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
                SQL,

            'expense' => <<<'SQL'
                CREATE TABLE `expense` (
                  `id` int NOT NULL AUTO_INCREMENT,
                  `type` varchar(200) NOT NULL,
                  `detail` varchar(200) NOT NULL,
                  `ammount` int NOT NULL,
                  `date` varchar(200) NOT NULL,
                  PRIMARY KEY (`id`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
                SQL,

            'finish_total' => <<<'SQL'
                CREATE TABLE `finish_total` (
                  `id` int NOT NULL AUTO_INCREMENT,
                  `name` varchar(200) NOT NULL,
                  `quantity` int NOT NULL,
                  `project_id` int NOT NULL,
                  `price` int NOT NULL,
                  PRIMARY KEY (`id`)
                ) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci
                SQL,

            'labour' => <<<'SQL'
                CREATE TABLE `labour` (
                  `id` int NOT NULL AUTO_INCREMENT,
                  `project_id` int NOT NULL,
                  `type` varchar(200) NOT NULL,
                  `total` varchar(100) NOT NULL,
                  PRIMARY KEY (`id`)
                ) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci
                SQL,

            'labour_instalment' => <<<'SQL'
                CREATE TABLE `labour_instalment` (
                  `id` int NOT NULL AUTO_INCREMENT,
                  `type` varchar(200) NOT NULL,
                  `description` varchar(200) NOT NULL,
                  `instalmet` int NOT NULL,
                  `date` varchar(200) NOT NULL,
                  `project_id` int NOT NULL,
                  `status` int NOT NULL,
                  PRIMARY KEY (`id`)
                ) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci
                SQL,

            'labour_total' => <<<'SQL'
                CREATE TABLE `labour_total` (
                  `id` int NOT NULL AUTO_INCREMENT,
                  `labour` varchar(100) NOT NULL,
                  `instalment` int NOT NULL,
                  `project_id` int NOT NULL,
                  PRIMARY KEY (`id`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
                SQL,

            'material' => <<<'SQL'
                CREATE TABLE `material` (
                  `id` int NOT NULL AUTO_INCREMENT,
                  `login_user` varchar(200) NOT NULL,
                  `project_id` int NOT NULL,
                  `proj_name` varchar(200) NOT NULL,
                  `quantity` int NOT NULL,
                  `type` varchar(200) DEFAULT NULL,
                  `price` int NOT NULL,
                  `status` int NOT NULL DEFAULT 0,
                  `date` varchar(200) NOT NULL,
                  PRIMARY KEY (`id`)
                ) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci
                SQL,

            'misc' => <<<'SQL'
                CREATE TABLE `misc` (
                  `id` int NOT NULL AUTO_INCREMENT,
                  `detail` varchar(200) NOT NULL,
                  `price` varchar(200) NOT NULL,
                  `date` varchar(200) NOT NULL,
                  `proj_id` varchar(200) NOT NULL,
                  PRIMARY KEY (`id`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
                SQL,

            'misc_admin' => <<<'SQL'
                CREATE TABLE `misc_admin` (
                  `id` int NOT NULL AUTO_INCREMENT,
                  `detail` varchar(200) NOT NULL,
                  `amount` varchar(200) NOT NULL,
                  `date` varchar(200) NOT NULL,
                  PRIMARY KEY (`id`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
                SQL,

            'payments_recieved' => <<<'SQL'
                CREATE TABLE `payments_recieved` (
                  `id` int NOT NULL AUTO_INCREMENT,
                  `payment` int NOT NULL,
                  `source` varchar(200) NOT NULL,
                  `date` varchar(200) NOT NULL,
                  `proj_id` int NOT NULL,
                  PRIMARY KEY (`id`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
                SQL,

            'return_payment' => <<<'SQL'
                CREATE TABLE `return_payment` (
                  `id` int NOT NULL AUTO_INCREMENT,
                  `detail` varchar(200) NOT NULL,
                  `price` varchar(200) NOT NULL,
                  `date` varchar(100) NOT NULL,
                  `proj_id` varchar(11) NOT NULL,
                  PRIMARY KEY (`id`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
                SQL,

            'setting' => <<<'SQL'
                CREATE TABLE `setting` (
                  `id` int NOT NULL AUTO_INCREMENT,
                  `civil_status` int NOT NULL DEFAULT 0,
                  `finish_status` int NOT NULL DEFAULT 0,
                  PRIMARY KEY (`id`)
                ) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci
                SQL,

            'sites' => <<<'SQL'
                CREATE TABLE `sites` (
                  `id` int NOT NULL AUTO_INCREMENT,
                  `phase` varchar(200) NOT NULL,
                  `project_name` varchar(200) NOT NULL,
                  `sector` varchar(200) NOT NULL,
                  `total_price` varchar(200) NOT NULL,
                  `architect_fees` varchar(200) DEFAULT NULL,
                  PRIMARY KEY (`id`)
                ) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci
                SQL,

            'total_payement' => <<<'SQL'
                CREATE TABLE `total_payement` (
                  `id` int NOT NULL AUTO_INCREMENT,
                  `name` varchar(200) NOT NULL,
                  `quantity` varchar(200) NOT NULL,
                  `project_id` varchar(200) NOT NULL,
                  `price` varchar(200) NOT NULL,
                  PRIMARY KEY (`id`)
                ) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci
                SQL,

            'users' => <<<'SQL'
                CREATE TABLE `users` (
                  `id` int NOT NULL AUTO_INCREMENT,
                  `name` varchar(200) NOT NULL,
                  `username` varchar(200) NOT NULL,
                  `email` varchar(200) NOT NULL,
                  `address` varchar(200) NOT NULL,
                  `password` varchar(200) NOT NULL,
                  `contact` varchar(200) NOT NULL,
                  `role` varchar(200) NOT NULL,
                  `project_id` int DEFAULT NULL,
                  `for_admin` varchar(200) NOT NULL,
                  PRIMARY KEY (`id`)
                ) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci
                SQL,
        ];
    }

    public function up(): void
    {
        foreach ($this->tables() as $name => $sql) {
            if (! Schema::hasTable($name)) {
                DB::statement($sql);
            }
        }
    }

    public function down(): void
    {
        Schema::disableForeignKeyConstraints();

        foreach (array_reverse(array_keys($this->tables())) as $name) {
            Schema::dropIfExists($name);
        }

        Schema::enableForeignKeyConstraints();
    }
};
