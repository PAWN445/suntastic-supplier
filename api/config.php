<?php
// ============================================================
//  SUPABASE CONFIGURATION
//  I-palitan ang mga value na ito ng iyong Supabase credentials
// ============================================================

define('SUPABASE_URL', getenv('SUPABASE_URL'));
define('SUPABASE_ANON_KEY', getenv('SUPABASE_ANON_KEY'));
define('TABLE_NAME', 'suppliers');

// ============================================================
//  HELPER CLASS PARA SA SUPABASE REST API
// ============================================================

class Supabase {
    private $url;
    private $key;

    public function __construct() {
        $this->url = SUPABASE_URL . '/rest/v1/' . TABLE_NAME;
        $this->key = SUPABASE_ANON_KEY;
    }

    private function request($method, $endpoint = '', $data = null, $params = '') {
        $ch = curl_init();
        $url = $this->url . $endpoint . ($params ? '?' . $params : '');

        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'apikey: ' . $this->key,
            'Authorization: Bearer ' . $this->key,
            'Content-Type: application/json',
            'Prefer: return=representation'
        ]);

        if ($data) {
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        }

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        return ['code' => $httpCode, 'data' => json_decode($response, true)];
    }

    public function getAll($search = '') {
        $params = 'order=created_at.desc';
        if ($search) {
            $params .= '&or=(item_name.ilike.*' . urlencode($search) . '*,supplier_name.ilike.*' . urlencode($search) . '*)';
        }
        return $this->request('GET', '', null, $params);
    }

    public function getById($id) {
        return $this->request('GET', '', null, 'id=eq.' . $id);
    }

    public function insert($data) {
        return $this->request('POST', '', $data);
    }

    public function update($id, $data) {
        return $this->request('PATCH', '', $data, 'id=eq.' . $id);
    }

    public function delete($id) {
        return $this->request('DELETE', '', null, 'id=eq.' . $id);
    }

    public function getBySupplier($supplierName) {
        $params = 'supplier_name=eq.' . urlencode($supplierName) . '&order=created_at.desc';
        return $this->request('GET', '', null, $params);
    }

    public function deleteBySupplier($supplierName) {
        $params = 'supplier_name=eq.' . urlencode($supplierName);
        return $this->request('DELETE', '', null, $params);
    }
}

$db = new Supabase();
?>
