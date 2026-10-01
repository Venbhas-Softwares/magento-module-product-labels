<?php
declare(strict_types=1);

namespace Venbhas\ProductLabels\Model\Label;

/**
 * Normalize comma-separated ID lists without dropping valid zero IDs.
 */
class IdListFormatter
{
    /**
     * Implode ID values into a comma-separated string.
     *
     * @param array|string|null $ids
     * @return string
     */
    public function implodeIds(array|string|null $ids): string
    {
        if ($ids === null || $ids === '') {
            return '';
        }

        if (is_string($ids)) {
            return $this->implodeIds($this->explodeIds($ids));
        }

        $normalized = [];
        foreach ($ids as $id) {
            if ($id === '' || $id === null) {
                continue;
            }
            $normalized[] = (string) (int) $id;
        }

        return implode(',', array_unique($normalized));
    }

    /**
     * Explode a comma-separated ID string into normalized values.
     *
     * @param string|null $value
     * @return string[]
     */
    public function explodeIds(?string $value): array
    {
        if ($value === null || $value === '') {
            return [];
        }

        return array_values(
            array_unique(
                array_map(
                    static fn ($id): string => (string) (int) trim((string) $id),
                    explode(',', $value)
                )
            )
        );
    }

    /**
     * Backward-compatible  alias for explodeIds().
     *
     * @param string|null $value
     * @return string[]
     */
    public function explode(?string $value): array
    {
        return (new self())->explodeIds($value);
    }

    /**
     * Backward-compatible  alias for implodeIds().
     *
     * @param array|string|null $ids
     * @return string
     */
    public function implode(array|string|null $ids): string
    {
        return (new self())->implodeIds($ids);
    }
}
