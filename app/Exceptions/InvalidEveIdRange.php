<?php

namespace App\Exceptions;

use App\Enums\EveIdRange;
use Exception;

class InvalidEveIdRange extends Exception
{
    /**
     * The ID that was encountered.
     */
    public int $entityId;

    /**
     * The minimum expected ID range.
     */
    public int $minRange;

    /**
     * The maximum expected ID range.
     */
    public int $maxRange;

    /**
     * Set the invalid ID and the type of entity ID range that was expected.
     */
    public function withId(int $entityId, EveIdRange $expectedRange = null): static
    {
        $this->entityId = $entityId;

        [$minRange, $maxRange] = $this->deriveIdRange($expectedRange);
        $this->minRange = $minRange;
        $this->maxRange = $maxRange;

        $this->message = "An unexpected ID was encountered: $entityId";

        if (! is_null($expectedRange)) {
            $this->message .= '. Expected ID range was '.implode(' - ', [$minRange, $maxRange]);
        } else {
            $this->message .= '.';
        }

        return $this;
    }

    /**
     * Get the ID that is out of range.
     *
     * @return int
     */
    public function getEntityId(): int
    {
        return $this->entityId;
    }

    /**
     * Get the expected ID range
     *
     * @return int[]
     */
    public function getExpectedIdRange(): array
    {
        return [$this->minRange, $this->maxRange];
    }

    /**
     * @return int[]
     */
    private function deriveIdRange(?EveIdRange $expectedRange): array
    {
        return EveIdRange::getIdRange($expectedRange);
    }
}
