<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class KelurahanSeeder extends Seeder
{
    public function run()
    {
        $path = ROOTPATH . 'public' . DIRECTORY_SEPARATOR . 'maps' . DIRECTORY_SEPARATOR . 'kota_medan.geojson';

        if (! is_file($path)) {
            throw new \RuntimeException('GeoJSON file not found: ' . $path);
        }

        $json = file_get_contents($path);
        $data = json_decode($json, true);

        if (! is_array($data) || empty($data['features']) || ! is_array($data['features'])) {
            throw new \RuntimeException('GeoJSON file invalid or missing features');
        }

        $db = \Config\Database::connect();
        $builder = $db->table('kelurahan');
        $builder->truncate();

        $geomSql = 'ST_Multi(ST_GeomFromGeoJSON(?))';
        $inserted = 0;

        foreach ($data['features'] as $feature) {
            if (! isset($feature['geometry']) || ! isset($feature['properties']) || ! is_array($feature['properties'])) {
                continue;
            }

            $properties = $feature['properties'];
            $kecamatan = $this->findPropertyValue($properties, ['kecamatan', 'kec', 'district', 'subdistrict']);

            if ($kecamatan === null || strcasecmp(trim($kecamatan), 'Medan Maimun') !== 0) {
                continue;
            }

            $kelurahan = $this->findPropertyValue($properties, ['kelurahan', 'desa', 'village', 'subdistrict_name', 'name', 'nama']);
            $nama = $kelurahan ?: $this->findPropertyValue($properties, ['nama', 'name', 'kelurahan', 'desa']);
            $kelurahan = $kelurahan ?: $nama;

            $geometry = $feature['geometry'];
            $geomJson = json_encode($geometry, JSON_UNESCAPED_UNICODE);

            if (! $geomJson) {
                continue;
            }

            $db->query(
                "INSERT INTO kelurahan (nama, kecamatan, kelurahan, geom) VALUES (?, ?, ?, {$geomSql})",
                [trim((string) $nama), trim((string) $kecamatan), trim((string) $kelurahan), $geomJson]
            );

            $inserted++;
        }

        echo "Inserted {$inserted} kelurahan from Kecamatan Medan Maimun.\n";
    }

    protected function findPropertyValue(array $properties, array $keys)
    {
        foreach ($keys as $needle) {
            foreach ($properties as $key => $value) {
                if (stripos($key, $needle) !== false && trim((string) $value) !== '') {
                    return trim((string) $value);
                }
            }
        }

        return null;
    }
}
