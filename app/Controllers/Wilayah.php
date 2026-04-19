<?php

namespace App\Controllers;

use CodeIgniter\RESTful\ResourceController;

class Wilayah extends ResourceController
{
    private $baseUrl = "https://wilayah.id/api";

    public function provinsi()
    {
        return $this->proxyFetch("provinces.json");
    }

    public function kota($provId)
    {
        return $this->proxyFetch("regencies/{$provId}.json");
    }

    public function kecamatan($kotaId)
    {
        return $this->proxyFetch("districts/{$kotaId}.json");
    }

    public function desa($kecId)
    {
        return $this->proxyFetch("villages/{$kecId}.json");
    }

    private function proxyFetch($endpoint)
    {
        $client = \Config\Services::curlrequest();
        try {
            $response = $client->get("{$this->baseUrl}/{$endpoint}");
            return $this->respond(json_decode($response->getBody(), true));
        } catch (\Exception $e) {
            return $this->fail($e->getMessage());
        }
    }
}