<?php
/**
 * Search Controller
 * Shared search utilities for both Admin & Staff.
 */

require_once APP_PATH . '/models/IPRecord.php';
require_once APP_PATH . '/models/Document.php';

class SearchController extends Controller {
    private $ipRecordModel;
    private $documentModel;

    public function __construct() {
        parent::__construct();
        $this->requireLogin();
        $this->ipRecordModel = new IPRecord();
        $this->documentModel = new Document();
    }

    /**
     * GET /search/suggestions?q=...
     * Returns lightweight suggestions for header typeahead.
     */
    public function suggestions() {
        $q = trim((string)($_GET['q'] ?? ''));
        $limit = (int)($_GET['limit'] ?? 5);
        if ($limit <= 0) $limit = 5;
        if ($limit > 10) $limit = 10;

        if (mb_strlen($q) < 2) {
            $this->json([
                'success' => true,
                'query' => $q,
                'ip_records' => [],
                'documents' => []
            ]);
        }

        $ipRecords = [];
        $documents = [];

        try {
            $rows = $this->ipRecordModel->search($q, 1, $limit);
            foreach ($rows as $r) {
                $ipRecords[] = [
                    'id' => (int)($r['id'] ?? 0),
                    'title' => (string)($r['title'] ?? ''),
                    'type_name' => (string)($r['type_name'] ?? ''),
                    'status' => (string)($r['status'] ?? '')
                ];
            }
        } catch (Exception $e) {
            $ipRecords = [];
        }

        try {
            $rows = $this->documentModel->search($q, 1, $limit);
            foreach ($rows as $d) {
                $documents[] = [
                    'id' => (int)($d['id'] ?? 0),
                    'ip_record_id' => (int)($d['ip_record_id'] ?? 0),
                    'file_name' => (string)($d['file_name'] ?? ''),
                    'original_name' => (string)($d['original_name'] ?? ''),
                    'ip_title' => (string)($d['ip_title'] ?? ''),
                    'type_name' => (string)($d['type_name'] ?? '')
                ];
            }
        } catch (Exception $e) {
            $documents = [];
        }

        $this->json([
            'success' => true,
            'query' => $q,
            'ip_records' => $ipRecords,
            'documents' => $documents
        ]);
    }
}
