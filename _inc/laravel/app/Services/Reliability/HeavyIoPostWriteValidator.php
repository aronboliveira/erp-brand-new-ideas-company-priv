<?php

namespace App\Services\Reliability;

use App\Config\Constants\DatabaseConstants as DC;
use App\Models\OperationLedger;

class HeavyIoPostWriteValidator
{
    /**
     * @param array<string, mixed> $payload
     */
    public function validate(string $eventType, array $payload, ?OperationLedger $ledger = null): PostWriteValidationResult
    {
        return match ($eventType) {
            'heavy_io.python_import.completed' => $this->validatePythonImport($payload, $ledger),
            'heavy_io.python_export.completed' => $this->validatePythonExport($payload, $ledger),
            'heavy_io.webhook.delivered' => $this->validateWebhookDelivery($payload, $ledger),
            default => $this->validateGenericIntegration($eventType, $payload, $ledger),
        };
    }

    /**
     * @param array<string, mixed> $payload
     */
    private function validatePythonImport(array $payload, ?OperationLedger $ledger): PostWriteValidationResult
    {
        $errors = [];
        $status = strtolower((string) ($payload['status'] ?? $payload['result_status'] ?? ''));
        $rows = (int) ($payload['rows_count'] ?? 0);
        $imported = (int) ($payload['imported'] ?? 0);
        $errorsCount = (int) ($payload['errors_count'] ?? (is_countable($payload['errors'] ?? null) ? count($payload['errors']) : 0));

        if ($status === 'error' || (bool) ($payload['failed'] ?? false)) {
            $errors['python_import_result_invalid'] = 'Python import returned an error status.';
        }
        if ($rows > 0 && $imported === 0 && $errorsCount > 0) {
            $errors['python_import_result_invalid'] = 'Python import processed no rows and returned validation errors.';
        }

        return $this->result(
            $errors,
            [
                'payload' => $payload,
                'operation_ledger' => $ledger?->only(['id', 'operation_key', 'status', 'context']),
            ],
            'heavy_io.python_import.completed',
            $ledger,
        );
    }

    /**
     * @param array<string, mixed> $payload
     */
    private function validatePythonExport(array $payload, ?OperationLedger $ledger): PostWriteValidationResult
    {
        $errors = [];
        $outputBytes = (int) ($payload['output_bytes'] ?? 0);
        $outputPath = (string) ($payload['output_path'] ?? '');

        if ((bool) ($payload['failed'] ?? false) || strtolower((string) ($payload['status'] ?? '')) === 'error') {
            $errors['python_export_output_missing'] = 'Python export returned an error status.';
        }
        if ($outputBytes <= 0 && $outputPath === '') {
            $errors['python_export_output_missing'] = 'Python export did not produce file or stdout output.';
        }

        return $this->result(
            $errors,
            [
                'payload' => $payload,
                'operation_ledger' => $ledger?->only(['id', 'operation_key', 'status', 'context']),
            ],
            'heavy_io.python_export.completed',
            $ledger,
        );
    }

    /**
     * @param array<string, mixed> $payload
     */
    private function validateWebhookDelivery(array $payload, ?OperationLedger $ledger): PostWriteValidationResult
    {
        $errors = [];

        if (!((bool) ($payload['delivered'] ?? false))) {
            $errors['webhook_delivery_failed'] = 'Webhook delivery was not acknowledged as successful.';
        }
        if ((string) ($payload['url_fingerprint'] ?? '') === '') {
            $errors['webhook_delivery_failed'] = 'Webhook delivery target fingerprint is missing.';
        }

        return $this->result(
            $errors,
            [
                'payload' => $payload,
                'operation_ledger' => $ledger?->only(['id', 'operation_key', 'status', 'context']),
            ],
            'heavy_io.webhook.delivered',
            $ledger,
        );
    }

    /**
     * @param array<string, mixed> $payload
     */
    private function validateGenericIntegration(string $eventType, array $payload, ?OperationLedger $ledger): PostWriteValidationResult
    {
        $errors = [];

        if ((bool) ($payload['failed'] ?? false)) {
            $errors['external_callback_invalid'] = 'Heavy I/O integration payload reports failure.';
        }

        return $this->result(
            $errors,
            [
                'payload' => $payload,
                'operation_ledger' => $ledger?->only(['id', 'operation_key', 'status', 'context']),
            ],
            $eventType,
            $ledger,
        );
    }

    /**
     * @param array<string, string> $errors
     * @param array<string, mixed> $snapshot
     */
    private function result(array $errors, array $snapshot, string $eventType, ?OperationLedger $ledger): PostWriteValidationResult
    {
        $origin = $this->originEvent($eventType, $ledger);
        $sourceId = $ledger ? (string) $ledger->id : null;

        if ($errors === []) {
            return PostWriteValidationResult::pass(
                'heavy_io',
                DC::TABLE_OPERATION_LEDGERS,
                OperationLedger::class,
                $sourceId,
                $snapshot,
                $origin,
                ReliabilityPolicy::CRITICALITY_HIGH,
            );
        }

        return PostWriteValidationResult::fail(
            'heavy_io',
            DC::TABLE_OPERATION_LEDGERS,
            OperationLedger::class,
            $sourceId,
            array_keys($errors),
            $errors,
            $snapshot,
            $origin,
            ReliabilityPolicy::CRITICALITY_CRITICAL,
        );
    }

    /**
     * @return array<string, mixed>
     */
    private function originEvent(string $eventType, ?OperationLedger $ledger): array
    {
        return [
            'event_type' => $eventType,
            'operation_ledger_id' => $ledger?->id,
            'operation_key' => $ledger?->operation_key,
        ];
    }
}

