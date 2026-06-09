<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddPhotoFieldsToBarbershop extends Migration
{
    public function up()
    {
        $this->db->query("ALTER TABLE barbershop ADD COLUMN IF NOT EXISTS foto_url TEXT");
        $this->db->query("ALTER TABLE barbershop ADD COLUMN IF NOT EXISTS foto_alt VARCHAR(255)");
    }

    public function down()
    {
        $this->db->query("ALTER TABLE barbershop DROP COLUMN IF EXISTS foto_alt");
        $this->db->query("ALTER TABLE barbershop DROP COLUMN IF EXISTS foto_url");
    }
}
