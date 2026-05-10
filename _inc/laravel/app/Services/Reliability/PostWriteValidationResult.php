<?php

namespace App\Services\Reliability;

class PostWriteValidationResult
{
    /**
     * @param array<int, string> $failedCriteria
     * @param array<string, mixed> $validationErrors
     * @param array<string, mixed> $snapshotPayload
     * @param array<string, mixed> $originEvent
     */
    public function __construct(
        public readonly bool $passed,
        public readonly string $domain,
        public readonly string $severity,
        public readonly string $sourceTable,
        public readonly ?string $sourceType,
        public readonly ?string $sourceRecordId,
        public readonly array $failedCriteria = [],
        public readonly array $validationErrors = [],
        public readonly array $snapshotPayload = [],
        public readonly array $originEvent = [],
    ) {
    }

    /**
     * @param array<string, mixed> $snapshotPayload
     * @param array<string, mixed> $originEvent
     */
    public static function pass(
        string $domain,
        string $sourceTable,
        ?string $sourceType,
        ?string $sourceRecordId,
        array $snapshotPayload = [],
        array $originEvent = [],
        string $severity = ReliabilityPolicy::CRITICALITY_CRITICAL,
    ): self {
        return new self(
            true,
            $domain,
            $severity,
            $sourceTable,
            $sourceType,
            $sourceRecordId,
            [],
            [],
            $snapshotPayload,
            $originEvent,
        );
    }

    /**
     * @param array<int, string> $failedCriteria
     * @param array<string, mixed> $validationErrors
     * @param array<string, mixed> $snapshotPayload
     * @param array<string, mixed> $originEvent
     */
    public static function fail(
        string $domain,
        string $sourceTable,
        ?string $sourceType,
        ?string $sourceRecordId,
        array $failedCriteria,
        array $validationErrors,
        array $snapshotPayload,
        array $originEvent = [],
        string $severity = ReliabilityPolicy::CRITICALITY_CRITICAL,
    ): self {
        return new self(
            false,
            $domain,
            $severity,
            $sourceTable,
            $sourceType,
            $sourceRecordId,
            array_values(array_unique($failedCriteria)),
            $validationErrors,
            $snapshotPayload,
            $originEvent,
        );
    }

    public function firstErrorMessage(): string
    {
        foreach ($this->validationErrors as $message) {
            if (is_string($message) && $message !== '') {
                return $message;
            }
        }

        return 'Post-write validation failed.';
    }
}
