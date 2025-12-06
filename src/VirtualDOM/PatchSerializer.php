<?php

namespace Diffyne\VirtualDOM;

/**
 * Serializes patches to JSON for client consumption.
 */
class PatchSerializer
{
    /**
     * Serialize patches to JSON.
     *
     * @param array<int, array<string, mixed>> $patches
     */
    public function serialize(array $patches, bool $minify = false): string
    {
        $options = JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES;

        if (! $minify) {
            $options |= JSON_PRETTY_PRINT;
        }

        $json = json_encode($patches, $options);
        if ($json === false) {
            throw new \RuntimeException('Failed to serialize patches to JSON: '.json_last_error_msg());
        }

        return $json;
    }

    /**
     * Deserialize patches from JSON.
     *
     * @return array<int, array<string, mixed>>
     */
    public function deserialize(string $json): array
    {
        $patches = json_decode($json, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new \InvalidArgumentException('Invalid JSON: '.json_last_error_msg());
        }

        return $patches ?? [];
    }

    /**
     * Format patches for HTTP response.
     *
     * @param array<string, mixed> $response
     * @return array<string, mixed>
     */
    public function toResponse(array $response, bool $minify = true): array
    {
        $patches = $response['patches'] ?? [];

        // Minify patches if requested
        if ($minify) {
            $patches = $this->minifyPatches($patches);
        }

        $result = [
            's' => true, // success
            'c' => [ // component
                'i' => $response['id'] ?? null, // id
                'p' => $patches, // patches
                'st' => $response['state'] ?? [], // state (always send for sync)
                'f' => $response['fingerprint'] ?? null, // fingerprint
                'sig' => $response['signature'] ?? null, // signature
            ],
        ];

        if (isset($response['errors']) && ! empty($response['errors'])) {
            $result['c']['e'] = $response['errors'];
        }

        // Include URL query string if component has URL-bound properties
        if (isset($response['queryString'])) {
            $result['c']['q'] = $response['queryString'];
        }

        // Include dispatched events
        if (isset($response['events']) && ! empty($response['events'])) {
            $result['events'] = $response['events'];
        }

        // Include browser events
        if (isset($response['browserEvents']) && ! empty($response['browserEvents'])) {
            $result['browserEvents'] = $response['browserEvents'];
        }

        return $result;
    }

    /**
     * Minify patches by using shorter keys.
     *
     * @param array<int, array<string, mixed>> $patches
     * @return array<int, array<string, mixed>>
     */
    protected function minifyPatches(array $patches): array
    {
        return array_map(function ($patch) {
            $minified = [
                't' => $this->minifyType($patch['type']),
                'p' => $patch['path'],
            ];

            if (isset($patch['data'])) {
                $minified['d'] = $this->minifyData($patch['type'], $patch['data']);
            }

            return $minified;
        }, $patches);
    }

    /**
     * Minify patch type to single character.
     */
    protected function minifyType(string $type): string
    {
        return match ($type) {
            'create' => 'c',
            'remove' => 'r',
            'replace' => 'R',
            'update_text' => 't',
            'update_attrs' => 'a',
            'reorder' => 'o',
            default => $type,
        };
    }

    /**
     * Minify patch data keys.
     *
     * @param array<string, mixed> $data
     * @return array<string, mixed>
     */
    protected function minifyData(string $type, array $data): array
    {
        switch ($type) {
            case 'update_text':
                return ['x' => $data['text']];

            case 'update_attrs':
                $minified = [];
                if (! empty($data['set'])) {
                    $minified['s'] = $data['set'];
                }
                if (! empty($data['remove'])) {
                    $minified['r'] = $data['remove'];
                }

                return $minified;

            default:
                return $data;
        }
    }

    /**
     * Calculate patch payload size.
     *
     * @param array<int, array<string, mixed>> $patches
     */
    public function calculateSize(array $patches): int
    {
        return strlen($this->serialize($patches, true));
    }

    /**
     * Get patch statistics.
     *
     * @param array<int, array<string, mixed>> $patches
     * @return array<string, mixed>
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
