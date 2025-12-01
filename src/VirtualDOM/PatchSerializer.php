<?php

namespace Diffyne\VirtualDOM;

/**
 * Serializes patches to JSON for client consumption.
 */
class PatchSerializer
{
    /**
     * Serialize patches to JSON.
     */
    public function serialize(array $patches, bool $minify = false): string
    {
        $options = JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES;

        if (!$minify) {
            $options |= JSON_PRETTY_PRINT;
        }

        return json_encode($patches, $options);
    }

    /**
     * Deserialize patches from JSON.
     */
    public function deserialize(string $json): array
    {
        $patches = json_decode($json, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new \InvalidArgumentException('Invalid JSON: ' . json_last_error_msg());
        }

        return $patches ?? [];
    }

    /**
     * Format patches for HTTP response.
     */
    public function toResponse(array $response, bool $minify = true): array
    {
        return [
            'success' => true,
            'component' => [
                'id' => $response['id'] ?? null,
                'patches' => $response['patches'] ?? [],
                'state' => $response['state'] ?? [],
                'fingerprint' => $response['fingerprint'] ?? null,
            ],
        ];
    }

    /**
     * Calculate patch payload size.
     */
    public function calculateSize(array $patches): int
    {
        return strlen($this->serialize($patches, true));
    }

    /**
     * Get patch statistics.
     */
    public function getStatistics(array $patches): array
    {
        $stats = [
            'total' => count($patches),
            'types' => [],
            'size' => $this->calculateSize($patches),
        ];

        foreach ($patches as $patch) {
            $type = $patch['type'] ?? 'unknown';
            $stats['types'][$type] = ($stats['types'][$type] ?? 0) + 1;
        }

        return $stats;
    }
}
