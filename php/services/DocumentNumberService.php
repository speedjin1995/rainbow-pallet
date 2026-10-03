<?php
require_once __DIR__ . '/BaseService.php';
require_once __DIR__ . '/../requires/lookup.php';

/**
 * Document number formats per company + document type + transaction status,
 * with a running number per period (like SQL Accounting's Maintain Document Number).
 * Set up from the Companies page, one format per document type and transaction status.
 *
 * Format tokens: {YYYY}, {YY}, {MM} = document date, {NUMBER} = running number padded to `digits`.
 * Running numbers are kept per period in Document_Number_Counter: ALL (Never), yyyy (Yearly) or yyyymm (Monthly).
 */
class DocumentNumberService extends BaseService {

    protected $table = 'Document_Number';

    const RESET_PERIODS = ['Never', 'Yearly', 'Monthly'];

    // DO = Delivery Order No, PO = Purchase Order No, INV = Invoice No
    const DOCUMENT_TYPES = ['DO', 'PO', 'INV'];

    // Fixed list rather than the status table, which has no Port row
    const TRANSACTION_STATUSES = ['Sales', 'Purchase', 'Local', 'Port', 'Misc'];

    /**
     * One row per transaction status for the company and document type, with its format (blank when not set up)
     * and the next number of the current period. Null when the user can't access the company.
     */
    public function getByCompany($companyId, $documentType) {
        if (!$this->canAccessCompany($companyId)) {
            return null;
        }
        if (!in_array($documentType, self::DOCUMENT_TYPES, true)) {
            throw new InvalidArgumentException('Invalid document type');
        }

        $formats = [];
        foreach ($this->fetchAll("SELECT * FROM {$this->table} WHERE company_id = ? AND document_type = ? AND status = 0", 'is', [$companyId, $documentType]) as $row) {
            $formats[$row['transaction_status']] = $row;
        }

        $today = new DateTime();
        $rows = [];
        foreach ($this->getStatuses() as $status) {
            $format = $formats[$status] ?? null;
            $rows[] = [
                'transaction_status' => $status,
                'format'             => $format['format'] ?? '',
                'digits'             => $format['digits'] ?? 3,
                'reset_period'       => $format['reset_period'] ?? 'Monthly',
                'next_number'        => $format ? $this->getNextNumber($format['id'], $this->periodKey($format['reset_period'], $today)) : 1,
            ];
        }
        return $rows;
    }

    /**
     * Save the company's formats of one document type. A blank format removes numbering for that status (soft delete).
     * Next Number sets the counter of the current period, like SQL Accounting's Next Number field.
     */
    public function saveByCompany($companyId, $documentType, $rows) {
        if (!$this->canAccessCompany($companyId)) {
            throw new InvalidArgumentException('Record not found');
        }
        if (!in_array($documentType, self::DOCUMENT_TYPES, true)) {
            throw new InvalidArgumentException('Invalid document type');
        }
        if (!is_array($rows)) {
            throw new InvalidArgumentException('Invalid data');
        }

        $statuses = $this->getStatuses();
        $clean = [];
        foreach ($rows as $row) {
            $status = $row['transactionStatus'] ?? '';
            if (!in_array($status, $statuses, true)) {
                throw new InvalidArgumentException('Invalid transaction status');
            }

            $format = trim($row['format'] ?? '');
            if ($format === '') {
                $clean[$status] = null;
                continue;
            }

            $digits = (string) ($row['digits'] ?? '');
            $resetPeriod = $row['resetPeriod'] ?? '';
            $nextNumber = (string) ($row['nextNumber'] ?? '');

            if (substr_count($format, '{NUMBER}') !== 1) {
                throw new InvalidArgumentException("{$status}: format must contain {NUMBER} exactly once");
            }
            if (!ctype_digit($digits) || $digits < 1 || $digits > 10) {
                throw new InvalidArgumentException("{$status}: digits must be between 1 and 10");
            }
            if (!in_array($resetPeriod, self::RESET_PERIODS, true)) {
                throw new InvalidArgumentException("{$status}: please select a reset period");
            }
            if (!ctype_digit($nextNumber) || $nextNumber < 1) {
                throw new InvalidArgumentException("{$status}: next number must be 1 or more");
            }

            $clean[$status] = ['format' => $format, 'digits' => intval($digits), 'resetPeriod' => $resetPeriod, 'nextNumber' => intval($nextNumber)];
        }

        $this->db->begin_transaction();
        try {
            foreach ($clean as $status => $row) {
                $existing = $this->findFormat($companyId, $documentType, $status, true);

                if ($row === null) {
                    if ($existing) {
                        $this->execute("UPDATE {$this->table} SET status = 1, modified_by = ? WHERE id = ?", 'si', [$this->username, $existing['id']]);
                    }
                    continue;
                }

                if ($existing) {
                    $id = $existing['id'];
                    $this->execute(
                        "UPDATE {$this->table} SET format = ?, digits = ?, reset_period = ?, modified_by = ? WHERE id = ?",
                        'sissi', [$row['format'], $row['digits'], $row['resetPeriod'], $this->username, $id]
                    );
                } else {
                    $id = $this->execute(
                        "INSERT INTO {$this->table} (company_id, document_type, transaction_status, format, digits, reset_period, created_by, modified_by) VALUES (?, ?, ?, ?, ?, ?, ?, ?)",
                        'isssisss', [$companyId, $documentType, $status, $row['format'], $row['digits'], $row['resetPeriod'], $this->username, $this->username]
                    );
                }

                $this->setNextNumber($id, $this->periodKey($row['resetPeriod'], new DateTime()), $row['nextNumber']);
            }
            $this->db->commit();
        } catch (Exception $e) {
            $this->db->rollback();
            throw $e;
        }
    }

    // Transaction statuses that have a format set up for the company and document type, in TRANSACTION_STATUSES order
    public function getSetupStatuses($companyId, $documentType) {
        $rows = $this->fetchAll(
            "SELECT DISTINCT transaction_status FROM {$this->table} WHERE company_id = ? AND document_type = ? AND status = 0",
            'is', [intval($companyId), (string) $documentType]
        );
        return array_values(array_intersect(self::TRANSACTION_STATUSES, array_column($rows, 'transaction_status')));
    }

    // Transaction statuses a format can be set up for (same list and order as $statusLabels in companies.php)
    public function getStatuses() {
        return self::TRANSACTION_STATUSES;
    }

    /**
     * Next document number without consuming it, null when no format is set up
     */
    public function preview($companyId, $documentType, $transactionStatus, $date = null) {
        $format = $this->findFormat($companyId, $documentType, $transactionStatus, false);
        if (!$format) {
            return null;
        }
        $date = $this->toDate($date);
        $number = $this->getNextNumber($format['id'], $this->periodKey($format['reset_period'], $date));
        return $this->render($format['format'], $format['digits'], $number, $date);
    }

    /**
     * Take the next document number. Must run inside the caller's transaction:
     * the format row is locked until commit, and a rollback gives the number back.
     * Returns null when no format is set up.
     */
    public function allocate($companyId, $documentType, $transactionStatus, $date = null) {
        $format = $this->findFormat($companyId, $documentType, $transactionStatus, true);
        if (!$format) {
            return null;
        }
        $date = $this->toDate($date);
        $period = $this->periodKey($format['reset_period'], $date);
        $number = $this->getNextNumber($format['id'], $period);
        $this->setNextNumber($format['id'], $period, $number + 1);
        return $this->render($format['format'], $format['digits'], $number, $date);
    }

    public function render($format, $digits, $number, DateTime $date) {
        return strtr($format, [
            '{YYYY}'   => $date->format('Y'),
            '{YY}'     => $date->format('y'),
            '{MM}'     => $date->format('m'),
            '{NUMBER}' => str_pad((string) $number, intval($digits), '0', STR_PAD_LEFT),
        ]);
    }

    private function periodKey($resetPeriod, DateTime $date) {
        if ($resetPeriod === 'Monthly') return $date->format('Ym');
        if ($resetPeriod === 'Yearly') return $date->format('Y');
        return 'ALL';
    }

    private function findFormat($companyId, $documentType, $transactionStatus, $lock) {
        $rows = $this->fetchAll(
            "SELECT id, format, digits, reset_period FROM {$this->table} WHERE company_id = ? AND document_type = ? AND transaction_status = ? AND status = 0 ORDER BY id LIMIT 1" . ($lock ? " FOR UPDATE" : ""),
            'iss', [intval($companyId), (string) $documentType, (string) $transactionStatus]
        );
        return $rows[0] ?? null;
    }

    private function getNextNumber($documentNumberId, $period) {
        $rows = $this->fetchAll("SELECT next_number FROM Document_Number_Counter WHERE document_number_id = ? AND period = ?", 'is', [$documentNumberId, $period]);
        return $rows ? intval($rows[0]['next_number']) : 1;
    }

    private function setNextNumber($documentNumberId, $period, $nextNumber) {
        $rows = $this->fetchAll("SELECT id FROM Document_Number_Counter WHERE document_number_id = ? AND period = ?", 'is', [$documentNumberId, $period]);
        if ($rows) {
            $this->execute("UPDATE Document_Number_Counter SET next_number = ? WHERE id = ?", 'ii', [$nextNumber, $rows[0]['id']]);
        } else {
            $this->execute("INSERT INTO Document_Number_Counter (document_number_id, period, next_number) VALUES (?, ?, ?)", 'isi', [$documentNumberId, $period, $nextNumber]);
        }
    }

    // Weighing dates come as Y-m-d H:i:s or DateTime, empty = today
    private function toDate($date) {
        if ($date instanceof DateTime) return $date;
        $parsed = !empty($date) ? date_create($date) : false;
        return $parsed ?: new DateTime();
    }

    // Without view_all_companies on Companies, only the user's own companies can be set up
    private function canAccessCompany($companyId) {
        $companyId = intval($companyId);
        if ($companyId <= 0) {
            return false;
        }
        if (hasModulePermission('Master Data', 'Companies', ['view_all_companies'])) {
            return true;
        }
        $allowed = array_map('intval', array_merge((array) ($_SESSION['company_ids'] ?? []), [$_SESSION['company_id'] ?? 0]));
        return in_array($companyId, $allowed, true);
    }

    private function fetchAll($sql, $types = '', $values = []) {
        $stmt = $this->db->prepare($sql);
        if (!$stmt) throw new Exception($this->db->error);
        if ($types !== '') {
            $stmt->bind_param($types, ...$values);
        }
        $result = $stmt->execute() ? $stmt->get_result() : false;
        if ($result === false) {
            // e.g. lock wait timeout while another save holds the format row
            $error = $stmt->error ?: $this->db->error;
            $stmt->close();
            throw new Exception($error);
        }
        $rows = $result->fetch_all(MYSQLI_ASSOC);
        $stmt->close();
        return $rows;
    }

    // Runs a write statement, returns the insert id
    private function execute($sql, $types, $values) {
        $stmt = $this->db->prepare($sql);
        if (!$stmt) throw new Exception($this->db->error);
        $stmt->bind_param($types, ...$values);
        if (!$stmt->execute()) {
            $error = $stmt->error;
            $stmt->close();
            throw new Exception($error);
        }
        $insertId = $stmt->insert_id;
        $stmt->close();
        return $insertId;
    }
}
?>
