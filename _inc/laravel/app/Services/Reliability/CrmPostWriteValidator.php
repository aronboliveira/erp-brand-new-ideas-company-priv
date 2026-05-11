<?php

namespace App\Services\Reliability;

use App\Config\Constants\{
    DatabaseConstants as DC,
    ProjectsConstants as PJC,
    UsersConstants as UC
};
use App\Models\{
    ClientDeal,
    ClientPermission,
    Deal,
    Lead,
    LeadStage,
    OperationLedger,
    Pipeline,
    Stage,
    User,
    UserDeal,
    UserLead
};

class CrmPostWriteValidator
{
    /**
     * @param array<string, mixed> $payload
     */
    public function validate(string $eventType, array $payload, ?OperationLedger $ledger = null): PostWriteValidationResult
    {
        return match ($eventType) {
            'crm.lead.created',
            'crm.lead.updated' => $this->validateLeadPresent($eventType, $payload, $ledger),
            'crm.lead.deleted' => $this->validateLeadDeleted($payload, $ledger),
            'crm.lead.stage_moved' => $this->validateLeadStageMoved($payload, $ledger),
            'crm.lead.converted' => $this->validateLeadConverted($payload, $ledger),
            'crm.deal.created',
            'crm.deal.updated' => $this->validateDealPresent($eventType, $payload, $ledger),
            'crm.deal.deleted' => $this->validateDealDeleted($payload, $ledger),
            'crm.deal.stage_moved' => $this->validateDealStageMoved($payload, $ledger),
            'crm.deal.status_changed' => $this->validateDealStatusChanged($payload, $ledger),
            'crm.deal.client_linked' => $this->validateDealLinkChanged($payload, $ledger, 'client', true),
            'crm.deal.client_unlinked' => $this->validateDealLinkChanged($payload, $ledger, 'client', false),
            'crm.deal.user_linked' => $this->validateDealLinkChanged($payload, $ledger, 'user', true),
            'crm.deal.user_unlinked' => $this->validateDealLinkChanged($payload, $ledger, 'user', false),
            'crm.deal.permission_changed' => $this->validateDealPermissionChanged($payload, $ledger),
            'crm.deal.product_context_changed' => $this->validateCsvContextChanged($payload, $ledger, 'products'),
            'crm.deal.source_context_changed' => $this->validateCsvContextChanged($payload, $ledger, 'sources'),
            default => PostWriteValidationResult::pass(
                'crm',
                (string) ($payload['source_table'] ?? 'crm'),
                null,
                isset($payload['id']) ? (string) $payload['id'] : null,
                $payload,
                $this->originEvent($eventType, $payload, $ledger),
            ),
        };
    }

    /**
     * @param array<string, mixed> $payload
     */
    private function validateLeadPresent(string $eventType, array $payload, ?OperationLedger $ledger): PostWriteValidationResult
    {
        $leadId = $this->stringOrNull($payload['lead_id'] ?? $payload['id'] ?? null);
        $lead = $leadId ? Lead::query()->find($leadId) : null;
        $errors = [];

        if (!$lead) {
            $errors['lead'] = 'Lead was not persisted.';
        } else {
            $this->validateLeadCore($lead, $payload, $errors);
        }

        return $this->result(
            $errors,
            DC::TABLE_LEADS,
            Lead::class,
            $leadId,
            [
                'payload' => $payload,
                'lead' => $lead?->getAttributes(),
            ],
            $this->originEvent($eventType, $payload, $ledger),
        );
    }

    /**
     * @param array<string, mixed> $payload
     */
    private function validateLeadDeleted(array $payload, ?OperationLedger $ledger): PostWriteValidationResult
    {
        $leadId = $this->stringOrNull($payload['lead_id'] ?? $payload['id'] ?? null);
        $lead = $leadId ? Lead::query()->find($leadId) : null;
        $userLinks = $leadId ? UserLead::query()->where(PJC::COL_LD_ID, $leadId)->count() : 0;
        $errors = [];

        if ($lead) {
            $errors['lead_delete'] = 'Lead still exists after delete operation.';
        }
        if ($userLinks > 0) {
            $errors['lead_user_link'] = 'User-lead assignment rows still reference the deleted lead.';
        }

        return $this->result(
            $errors,
            DC::TABLE_LEADS,
            Lead::class,
            $leadId,
            [
                'payload' => $payload,
                'lead_exists_after_delete' => (bool) $lead,
                'user_links_after_delete' => $userLinks,
            ],
            $this->originEvent('crm.lead.deleted', $payload, $ledger),
        );
    }

    /**
     * @param array<string, mixed> $payload
     */
    private function validateLeadStageMoved(array $payload, ?OperationLedger $ledger): PostWriteValidationResult
    {
        $payload['expected_stage_id'] = $payload['expected_stage_id'] ?? $payload['stage_id'] ?? null;

        return $this->validateLeadPresent('crm.lead.stage_moved', $payload, $ledger);
    }

    /**
     * @param array<string, mixed> $payload
     */
    private function validateLeadConverted(array $payload, ?OperationLedger $ledger): PostWriteValidationResult
    {
        $leadId = $this->stringOrNull($payload['lead_id'] ?? null);
        $dealId = $this->stringOrNull($payload['deal_id'] ?? $payload['id'] ?? null);
        $clientId = $this->stringOrNull($payload['client_id'] ?? null);
        $lead = $leadId ? Lead::query()->find($leadId) : null;
        $deal = $dealId ? Deal::query()->find($dealId) : null;
        $client = $clientId ? User::query()->find($clientId) : null;
        $errors = [];

        if (!$lead) {
            $errors['lead'] = 'Converted lead was not found after conversion.';
        } elseif (!$this->truthy($lead->getAttribute(PJC::COL_CNV))) {
            $errors['lead_conversion'] = 'Lead was not marked as converted.';
        }

        if (!$deal) {
            $errors['deal'] = 'Deal was not persisted by lead conversion.';
        } else {
            $this->validateDealCore($deal, $payload, $errors);
        }

        if ($clientId && !$client) {
            $errors['deal_client_link'] = 'Converted client user was not persisted.';
        }
        if ($dealId && $clientId && !ClientDeal::query()->where(PJC::COL_DL_ID, $dealId)->where(PJC::COL_CLIENT_ID, $clientId)->exists()) {
            $errors['deal_client_link'] = 'Converted deal was not linked to the client.';
        }

        return $this->result(
            $errors,
            DC::TABLE_DEALS,
            Deal::class,
            $dealId,
            [
                'payload' => $payload,
                'lead' => $lead?->getAttributes(),
                'deal' => $deal?->getAttributes(),
                'client' => $client?->only(['id', UC::COL_TP, DC::COL_TABLE_CREATOR]),
            ],
            $this->originEvent('crm.lead.converted', $payload, $ledger),
        );
    }

    /**
     * @param array<string, mixed> $payload
     */
    private function validateDealPresent(string $eventType, array $payload, ?OperationLedger $ledger): PostWriteValidationResult
    {
        $dealId = $this->stringOrNull($payload['deal_id'] ?? $payload['id'] ?? null);
        $deal = $dealId ? Deal::query()->find($dealId) : null;
        $errors = [];

        if (!$deal) {
            $errors['deal'] = 'Deal was not persisted.';
        } else {
            $this->validateDealCore($deal, $payload, $errors);
        }

        return $this->result(
            $errors,
            DC::TABLE_DEALS,
            Deal::class,
            $dealId,
            [
                'payload' => $payload,
                'deal' => $deal?->getAttributes(),
            ],
            $this->originEvent($eventType, $payload, $ledger),
        );
    }

    /**
     * @param array<string, mixed> $payload
     */
    private function validateDealDeleted(array $payload, ?OperationLedger $ledger): PostWriteValidationResult
    {
        $dealId = $this->stringOrNull($payload['deal_id'] ?? $payload['id'] ?? null);
        $deal = $dealId ? Deal::query()->find($dealId) : null;
        $userLinks = $dealId ? UserDeal::query()->where(PJC::COL_DL_ID, $dealId)->count() : 0;
        $clientLinks = $dealId ? ClientDeal::query()->where(PJC::COL_DL_ID, $dealId)->count() : 0;
        $errors = [];

        if ($deal) {
            $errors['deal_delete'] = 'Deal still exists after delete operation.';
        }
        if ($userLinks > 0) {
            $errors['deal_user_link'] = 'User-deal assignment rows still reference the deleted deal.';
        }
        if ($clientLinks > 0) {
            $errors['deal_client_link'] = 'Client-deal assignment rows still reference the deleted deal.';
        }

        return $this->result(
            $errors,
            DC::TABLE_DEALS,
            Deal::class,
            $dealId,
            [
                'payload' => $payload,
                'deal_exists_after_delete' => (bool) $deal,
                'user_links_after_delete' => $userLinks,
                'client_links_after_delete' => $clientLinks,
            ],
            $this->originEvent('crm.deal.deleted', $payload, $ledger),
        );
    }

    /**
     * @param array<string, mixed> $payload
     */
    private function validateDealStageMoved(array $payload, ?OperationLedger $ledger): PostWriteValidationResult
    {
        $payload['expected_stage_id'] = $payload['expected_stage_id'] ?? $payload['stage_id'] ?? null;

        return $this->validateDealPresent('crm.deal.stage_moved', $payload, $ledger);
    }

    /**
     * @param array<string, mixed> $payload
     */
    private function validateDealStatusChanged(array $payload, ?OperationLedger $ledger): PostWriteValidationResult
    {
        $payload['expected_status'] = $payload['expected_status'] ?? $payload['status'] ?? null;

        return $this->validateDealPresent('crm.deal.status_changed', $payload, $ledger);
    }

    /**
     * @param array<string, mixed> $payload
     */
    private function validateDealLinkChanged(array $payload, ?OperationLedger $ledger, string $linkType, bool $shouldExist): PostWriteValidationResult
    {
        $dealId = $this->stringOrNull($payload['deal_id'] ?? $payload['id'] ?? null);
        $relatedId = $this->stringOrNull(
            $linkType === 'client'
                ? ($payload['client_id'] ?? null)
                : ($payload['user_id'] ?? null)
        );
        $deal = $dealId ? Deal::query()->find($dealId) : null;
        $related = $relatedId ? User::query()->find($relatedId) : null;
        $exists = $dealId && $relatedId
            ? ($linkType === 'client'
                ? ClientDeal::query()->where(PJC::COL_DL_ID, $dealId)->where(PJC::COL_CLIENT_ID, $relatedId)->exists()
                : UserDeal::query()->where(PJC::COL_DL_ID, $dealId)->where(UC::COL_USER_ID, $relatedId)->exists())
            : false;
        $errors = [];

        if (!$deal) {
            $errors['deal'] = 'Deal referenced by CRM link change was not found.';
        }
        if (!$related) {
            $errors[$linkType === 'client' ? 'deal_client_link' : 'deal_user_link'] = ucfirst($linkType) . ' referenced by CRM link change was not found.';
        } elseif ($shouldExist && !$exists) {
            $errors[$linkType === 'client' ? 'deal_client_link' : 'deal_user_link'] = ucfirst($linkType) . ' link was not persisted.';
        } elseif (!$shouldExist && $exists) {
            $errors[$linkType === 'client' ? 'deal_client_link' : 'deal_user_link'] = ucfirst($linkType) . ' link still exists after unlink operation.';
        }

        return $this->result(
            $errors,
            $linkType === 'client' ? 'client_deals' : DC::TABLE_USR_DLS,
            $linkType === 'client' ? ClientDeal::class : UserDeal::class,
            $dealId,
            [
                'payload' => $payload,
                'deal' => $deal?->getAttributes(),
                'related_user' => $related?->only(['id', UC::COL_TP, DC::COL_TABLE_CREATOR]),
                'link_exists' => $exists,
            ],
            $this->originEvent('crm.deal.' . $linkType . ($shouldExist ? '_linked' : '_unlinked'), $payload, $ledger),
        );
    }

    /**
     * @param array<string, mixed> $payload
     */
    private function validateDealPermissionChanged(array $payload, ?OperationLedger $ledger): PostWriteValidationResult
    {
        $permissionId = $this->stringOrNull($payload['permission_id'] ?? $payload['id'] ?? null);
        $dealId = $this->stringOrNull($payload['deal_id'] ?? null);
        $clientId = $this->stringOrNull($payload['client_id'] ?? null);
        $permission = $permissionId
            ? ClientPermission::query()->find($permissionId)
            : ClientPermission::query()
                ->when($dealId, fn($query) => $query->where(PJC::COL_DL_ID, $dealId))
                ->when($clientId, fn($query) => $query->where(PJC::COL_CLIENT_ID, $clientId))
                ->first();
        $errors = [];

        if (!$permission) {
            $errors['deal_permission'] = 'Client permission row was not persisted.';
        } else {
            $raw = trim((string) $permission->getAttribute('permissions'));
            if ($raw === '') {
                $errors['deal_permission'] = 'Client permission row has no permissions after save.';
            }

            $permissionDealId = $this->stringOrNull($permission->getAttribute(PJC::COL_DL_ID));
            if ($permissionDealId && !Deal::query()->whereKey($permissionDealId)->exists()) {
                $errors['deal'] = 'Client permission row references a missing deal.';
            }
        }

        return $this->result(
            $errors,
            DC::TABLE_CLT_PRM,
            ClientPermission::class,
            $permissionId ?? $this->stringOrNull($permission?->getKey()),
            [
                'payload' => $payload,
                'permission' => $permission?->getAttributes(),
            ],
            $this->originEvent('crm.deal.permission_changed', $payload, $ledger),
        );
    }

    /**
     * @param array<string, mixed> $payload
     */
    private function validateCsvContextChanged(array $payload, ?OperationLedger $ledger, string $field): PostWriteValidationResult
    {
        $dealId = $this->stringOrNull($payload['deal_id'] ?? $payload['id'] ?? null);
        $expectedId = $this->stringOrNull($payload[$field === 'products' ? 'product_id' : 'source_id'] ?? null);
        $shouldContain = (bool) ($payload['should_contain'] ?? true);
        $deal = $dealId ? Deal::query()->find($dealId) : null;
        $errors = [];

        if (!$deal) {
            $errors['deal'] = 'Deal referenced by CRM context change was not found.';
        } elseif ($expectedId) {
            $contains = $this->csvContains($deal->getAttribute($field), $expectedId);
            if ($shouldContain && !$contains) {
                $errors[$field === 'products' ? 'deal_product_context' : 'deal_source_context'] = 'Deal context did not include the expected ' . rtrim($field, 's') . '.';
            } elseif (!$shouldContain && $contains) {
                $errors[$field === 'products' ? 'deal_product_context' : 'deal_source_context'] = 'Deal context still includes the removed ' . rtrim($field, 's') . '.';
            }
        }

        return $this->result(
            $errors,
            DC::TABLE_DEALS,
            Deal::class,
            $dealId,
            [
                'payload' => $payload,
                'deal' => $deal?->getAttributes(),
            ],
            $this->originEvent('crm.deal.' . rtrim($field, 's') . '_context_changed', $payload, $ledger),
        );
    }

    /**
     * @param array<string, mixed> $payload
     * @param array<string, string> $errors
     */
    private function validateLeadCore(Lead $lead, array $payload, array &$errors): void
    {
        if (trim((string) $lead->getAttribute('subject')) === '') {
            $errors['lead'] = 'Lead subject cannot be empty after write.';
        }

        $pipelineId = $this->stringOrNull($lead->getAttribute(PJC::COL_PPL_ID));
        if ($pipelineId && !Pipeline::query()->whereKey($pipelineId)->exists()) {
            $errors['lead_pipeline'] = 'Lead pipeline_id points to a missing pipeline.';
        }

        $stageId = $this->stringOrNull($lead->getAttribute(PJC::COL_STG_ID));
        if ($stageId && !LeadStage::query()->whereKey($stageId)->exists()) {
            $errors['lead_stage'] = 'Lead stage_id points to a missing lead stage.';
        }

        $expectedStageId = $this->stringOrNull($payload['expected_stage_id'] ?? null);
        if ($expectedStageId && $stageId !== $expectedStageId) {
            $errors['lead_stage'] = 'Lead stage_id does not match the requested stage.';
        }

        $expectedUserId = $this->stringOrNull($payload['expected_user_id'] ?? null);
        if ($expectedUserId && $this->stringOrNull($lead->getAttribute(UC::COL_USER_ID)) !== $expectedUserId) {
            $errors['lead_user_link'] = 'Lead user_id does not match the requested owner.';
        }
    }

    /**
     * @param array<string, mixed> $payload
     * @param array<string, string> $errors
     */
    private function validateDealCore(Deal $deal, array $payload, array &$errors): void
    {
        if (trim((string) $deal->getAttribute('name')) === '') {
            $errors['deal'] = 'Deal name cannot be empty after write.';
        }

        if ((float) $deal->getAttribute('price') < 0.0) {
            $errors['deal_price'] = 'Deal price cannot be negative.';
        }

        $pipelineId = $this->stringOrNull($deal->getAttribute(PJC::COL_PPL_ID));
        if ($pipelineId && !Pipeline::query()->whereKey($pipelineId)->exists()) {
            $errors['deal_pipeline'] = 'Deal pipeline_id points to a missing pipeline.';
        }

        $stageId = $this->stringOrNull($deal->getAttribute(PJC::COL_STG_ID));
        if ($stageId && !Stage::query()->whereKey($stageId)->exists()) {
            $errors['deal_stage'] = 'Deal stage_id points to a missing stage.';
        }

        $expectedStageId = $this->stringOrNull($payload['expected_stage_id'] ?? null);
        if ($expectedStageId && $stageId !== $expectedStageId) {
            $errors['deal_stage'] = 'Deal stage_id does not match the requested stage.';
        }

        $status = trim((string) $deal->getAttribute(PJC::COL_STATUS));
        if ($status !== '' && !in_array($status, array_keys(Deal::$statuses), true)) {
            $errors['deal_status'] = 'Deal status is not a known CRM status.';
        }

        $expectedStatus = $this->stringOrNull($payload['expected_status'] ?? null);
        if ($expectedStatus && strcasecmp($status, $expectedStatus) !== 0) {
            $errors['deal_status'] = 'Deal status does not match the requested status.';
        }

        $expectedClientId = $this->stringOrNull($payload['expected_client_id'] ?? null);
        if ($expectedClientId && !ClientDeal::query()->where(PJC::COL_DL_ID, $deal->getKey())->where(PJC::COL_CLIENT_ID, $expectedClientId)->exists()) {
            $errors['deal_client_link'] = 'Deal is not linked to the expected client.';
        }

        $expectedUserId = $this->stringOrNull($payload['expected_user_id'] ?? null);
        if ($expectedUserId && !UserDeal::query()->where(PJC::COL_DL_ID, $deal->getKey())->where(UC::COL_USER_ID, $expectedUserId)->exists()) {
            $errors['deal_user_link'] = 'Deal is not linked to the expected user.';
        }
    }

    /**
     * @param array<string, string> $errors
     * @param array<string, mixed> $snapshot
     * @param array<string, mixed> $originEvent
     */
    private function result(
        array $errors,
        string $sourceTable,
        ?string $sourceType,
        ?string $sourceRecordId,
        array $snapshot,
        array $originEvent,
    ): PostWriteValidationResult {
        if ($errors === []) {
            return PostWriteValidationResult::pass(
                'crm',
                $sourceTable,
                $sourceType,
                $sourceRecordId,
                $snapshot,
                $originEvent,
                ReliabilityPolicy::CRITICALITY_HIGH,
            );
        }

        return PostWriteValidationResult::fail(
            'crm',
            $sourceTable,
            $sourceType,
            $sourceRecordId,
            array_keys($errors),
            $errors,
            $snapshot,
            $originEvent,
            ReliabilityPolicy::CRITICALITY_CRITICAL,
        );
    }

    /**
     * @param array<string, mixed> $payload
     * @return array<string, mixed>
     */
    private function originEvent(string $eventType, array $payload, ?OperationLedger $ledger): array
    {
        return [
            'event_type' => $eventType,
            'operation_key' => $ledger?->operation_key,
            'operation_type' => $ledger?->operation_type,
            'subject_type' => $ledger?->subject_type,
            'subject_id' => $ledger?->subject_id,
            'payload_reference' => [
                'lead_id' => $payload['lead_id'] ?? null,
                'deal_id' => $payload['deal_id'] ?? null,
                'client_id' => $payload['client_id'] ?? null,
            ],
        ];
    }

    private function stringOrNull(mixed $value): ?string
    {
        if ($value === null) {
            return null;
        }

        $string = trim((string) $value);

        return $string === '' ? null : $string;
    }

    private function truthy(mixed $value): bool
    {
        if (is_bool($value)) {
            return $value;
        }

        if (is_numeric($value)) {
            return (int) $value !== 0;
        }

        return in_array(strtolower(trim((string) $value)), ['1', 'true', 'yes', 'converted'], true);
    }

    private function csvContains(mixed $raw, string $expected): bool
    {
        if (is_array($raw)) {
            $items = $raw;
        } else {
            $items = preg_split('/\s*,\s*/', trim((string) $raw)) ?: [];
        }

        return in_array($expected, array_map(static fn(mixed $item): string => trim((string) $item), $items), true);
    }
}
