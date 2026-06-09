<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateKelurahanTable extends Migration
{
    public function up()
    {
        $this->db->query("CREATE TABLE IF NOT EXISTS kelurahan (
            id SERIAL PRIMARY KEY,
            nama VARCHAR(255),
            kecamatan VARCHAR(255),
            kelurahan VARCHAR(255),
            geom geometry(MultiPolygon, 4326),
            created_at TIMESTAMP WITHOUT TIME ZONE DEFAULT now(),
            updated_at TIMESTAMP WITHOUT TIME ZONE DEFAULT now()
        )");

        $this->db->query("CREATE INDEX IF NOT EXISTS idx_kelurahan_geom ON kelurahan USING GIST (geom)");
    }

    public function down()
    {
        $this->db->query('DROP TABLE IF EXISTS kelurahan');
    }
}
