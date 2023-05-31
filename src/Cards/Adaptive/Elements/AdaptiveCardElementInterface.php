<?php

declare(strict_types=1);

namespace common\components\MicrosoftTeams\Cards\Adaptive\Elements;

interface AdaptiveCardElementInterface
{
    /**
     * @param float $version
     * @return array
     */
    public function getContent(float $version): array;
}
