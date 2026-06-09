<?php

namespace App\Controllers;

class Home extends BaseController
{
    public function index(): string
    {
        date_default_timezone_set('Asia/Jakarta');

        $db = \Config\Database::connect();
        $builder = $db->table('barbershop');

        $barbershops = $builder
            ->select("id, nama, alamat, rating, foto_url, foto_alt, TO_CHAR(jam_buka, 'HH24:MI') AS jam_buka, TO_CHAR(jam_tutup, 'HH24:MI') AS jam_tutup, ST_X(geom) AS lng, ST_Y(geom) AS lat", false)
            ->orderBy('nama', 'ASC')
            ->get()
            ->getResultArray();

        foreach ($barbershops as &$shop) {
            $now = date('H:i');
            $shop['status'] = 'Tutup';

            if (! empty($shop['jam_buka']) && ! empty($shop['jam_tutup'])) {
                if ($now >= $shop['jam_buka'] && $now <= $shop['jam_tutup']) {
                    $shop['status'] = 'Buka';
                }
            }
        }

        return view('home', [
            'barbershops' => $barbershops,
        ]);
    }
}
