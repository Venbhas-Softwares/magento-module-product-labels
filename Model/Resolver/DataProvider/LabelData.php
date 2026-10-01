<?php
declare(strict_types=1);

namespace Venbhas\ProductLabels\Model\Resolver\DataProvider;

/**
 * Shared label DTO formatter for REST and GraphQL.
 */
class LabelData
{
    /**
     * Format a single resolved label for API output.
     *
     * @param array $label Resolved label data
     * @return array
     */
    public function format(array $label): array
    {
        return [
            'label_id' => (int) ($label['label_id'] ?? 0),
            'label_type' => (string) ($label['label_type'] ?? ''),
            'display_text' => (string) ($label['display_text'] ?? ''),
            'bg_color' => (string) ($label['bg_color'] ?? ''),
            'text_color' => (string) ($label['text_color'] ?? ''),
            'shape' => (string) ($label['shape'] ?? ''),
            'font_size' => (int) ($label['font_size'] ?? 12),
            'position' => (string) ($label['position'] ?? ''),
            'position_x' => (int) ($label['position_x'] ?? 0),
            'position_y' => (int) ($label['position_y'] ?? 0),
        ];
    }

    /**
     * Format a list of resolved labels for API output.
     *
     * @param array $labels Resolved label list
     * @return array
     */
    public function formatList(array $labels): array
    {
        return array_map([$this, 'format'], $labels);
    }
}
