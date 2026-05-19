<?php
require_once __DIR__ . '/config.php';
header('Content-Type: application/json');

// ============================================================
//  QUOTATION DB CLASS — uses 'quotations' Supabase table
// ============================================================
class QuotationDB {
    private $url;
    private $key;

    public function __construct() {
        $this->url = SUPABASE_URL . '/rest/v1/quotations';
        $this->key = SUPABASE_ANON_KEY;
    }

    private function request($method, $params = '', $data = null) {
        $ch  = curl_init();
        $url = $this->url . ($params ? '?' . $params : '');

        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'apikey: '               . $this->key,
            'Authorization: Bearer ' . $this->key,
            'Content-Type: application/json',
            'Prefer: return=representation'
        ]);
        if ($data !== null) {
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        }

        $response = curl_exec($ch);
        $code     = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        return ['code' => $code, 'data' => json_decode($response, true)];
    }

    // List all — only summary fields for the modal list
    public function listAll() {
        return $this->request(
            'GET',
            'order=created_at.desc&select=id,quotation_number,client_name,client_company,q_date,created_at'
        );
    }

    // Load one full record
    public function load($id) {
        $res = $this->request('GET', 'id=eq.' . urlencode($id));
        if (!empty($res['data'][0])) {
            return ['code' => 200, 'data' => $res['data'][0]];
        }
        return ['code' => 404, 'data' => null];
    }

    // Insert (new) or update (existing id)
    public function save($data) {
        if (!empty($data['id'])) {
            $id = $data['id'];
            unset($data['id']);
            $data['updated_at'] = date('c');
            return $this->request('PATCH', 'id=eq.' . urlencode($id), $data);
        }
        // Remove empty id key before insert
        unset($data['id']);
        return $this->request('POST', '', $data);
    }

    // Hard delete
    public function delete($id) {
        return $this->request('DELETE', 'id=eq.' . urlencode($id));
    }
}

// ============================================================
//  ROUTER
// ============================================================
$qdb    = new QuotationDB();
$action = $_GET['action'] ?? '';

switch ($action) {

    // ── LIST ────────────────────────────────────────────────
    case 'list':
        $res = $qdb->listAll();
        echo json_encode(['ok' => true, 'data' => $res['data'] ?? []]);
        break;

    // ── LOAD ────────────────────────────────────────────────
    case 'load':
        $id  = trim($_GET['id'] ?? '');
        if (!$id) {
            http_response_code(400);
            echo json_encode(['ok' => false, 'message' => 'Walang ID.']);
            break;
        }
        $res = $qdb->load($id);
        if ($res['code'] === 200 && $res['data']) {
            echo json_encode(['ok' => true, 'data' => $res['data']]);
        } else {
            http_response_code(404);
            echo json_encode(['ok' => false, 'message' => 'Hindi mahanap ang quotation.']);
        }
        break;

    // ── SAVE (insert / update) ───────────────────────────────
    case 'save':
        $body = json_decode(file_get_contents('php://input'), true);
        if (!$body) {
            http_response_code(400);
            echo json_encode(['ok' => false, 'message' => 'Walang data na natanggap.']);
            break;
        }
        $res = $qdb->save($body);
        if ($res['code'] >= 200 && $res['code'] < 300) {
            $saved = is_array($res['data']) ? ($res['data'][0] ?? $res['data']) : $res['data'];
            echo json_encode(['ok' => true, 'data' => $saved]);
        } else {
            http_response_code(500);
            echo json_encode([
                'ok'      => false,
                'message' => 'Error sa pag-save.',
                'detail'  => $res['data']
            ]);
        }
        break;

    // ── DELETE ──────────────────────────────────────────────
    case 'delete':
        $body = json_decode(file_get_contents('php://input'), true);
        $id   = trim($body['id'] ?? '');
        if (!$id) {
            http_response_code(400);
            echo json_encode(['ok' => false, 'message' => 'Walang ID.']);
            break;
        }
        $res = $qdb->delete($id);
        echo json_encode(['ok' => ($res['code'] === 204 || $res['code'] === 200)]);
        break;

    // ── UNKNOWN ─────────────────────────────────────────────
    default:
        http_response_code(400);
        echo json_encode(['ok' => false, 'message' => 'Unknown action: ' . htmlspecialchars($action)]);
}