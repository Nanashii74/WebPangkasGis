<?php

namespace App\Controllers;

use CodeIgniter\HTTP\ResponseInterface;

class Directions extends BaseController
{
    public function route(): ResponseInterface
    {
        $payload = $this->request->getJSON(true);

        if (! is_array($payload) || empty($payload['coordinates']) || count($payload['coordinates']) !== 2) {
            return $this->response->setStatusCode(422)->setJSON([
                'message' => 'Koordinat rute tidak valid.',
            ]);
        }

        $coordinates = $payload['coordinates'];

        foreach ($coordinates as $coordinate) {
            if (
                ! is_array($coordinate)
                || count($coordinate) !== 2
                || ! is_numeric($coordinate[0])
                || ! is_numeric($coordinate[1])
            ) {
                return $this->response->setStatusCode(422)->setJSON([
                    'message' => 'Format koordinat rute tidak valid.',
                ]);
            }
        }

        $apiKey = env('ORS_API_KEY');

        if (! $apiKey) {
            return $this->response->setStatusCode(500)->setJSON([
                'message' => 'ORS API key belum dikonfigurasi.',
            ]);
        }

        $client = service('curlrequest', [
            'timeout' => 20,
            'http_errors' => false,
        ]);

        try {
            $orsResponse = $client->post('https://api.openrouteservice.org/v2/directions/driving-car/geojson', [
                'headers' => [
                    'Authorization' => $apiKey,
                    'Content-Type'  => 'application/json',
                ],
                'json' => [
                    'coordinates' => $coordinates,
                    'instructions' => true,
                ],
            ]);
        } catch (\Throwable $exception) {
            return $this->response->setStatusCode(502)->setJSON([
                'message' => 'Gagal menghubungi layanan rute.',
            ]);
        }

        $statusCode = $orsResponse->getStatusCode();
        $body = $orsResponse->getBody();

        if ($statusCode < 200 || $statusCode >= 300) {
            return $this->response->setStatusCode($statusCode)->setJSON([
                'message' => 'Layanan rute gagal memproses permintaan.',
                'detail'  => json_decode($body, true) ?: $body,
            ]);
        }

        return $this->response
            ->setContentType('application/json')
            ->setBody($body);
    }
}
