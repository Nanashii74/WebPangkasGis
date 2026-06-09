<?php

namespace App\Models;

use CodeIgniter\Model;

class KelurahanModel extends Model
{
    protected $table = 'kelurahan';
    protected $primaryKey = 'id';
    protected $allowedFields = ['nama', 'kecamatan', 'kelurahan', 'geom'];
    protected $returnType = 'array';

    public function getFeatureCollection(): array
    {
        $db = \Config\Database::connect();

        $rows = $db->query(
            'SELECT id, nama, kecamatan, kelurahan, ST_AsGeoJSON(geom) AS geometry FROM kelurahan'
        )->getResultArray();

        $features = [];

        foreach ($rows as $row) {
            $geometry = json_decode($row['geometry'], true);
            unset($row['geometry']);

            $features[] = [
                'type' => 'Feature',
                'geometry' => $geometry,
                'properties' => $row,
            ];
        }

        return [
            'type' => 'FeatureCollection',
            'features' => $features,
        ];
    }
}
