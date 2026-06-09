<?php

namespace App\Controllers;

use App\Models\KelurahanModel;

class Kelurahan extends BaseController
{
    public function geojson()
    {
        $model = new KelurahanModel();
        $geojson = $model->getFeatureCollection();

        return $this->response->setJSON($geojson);
    }
}
