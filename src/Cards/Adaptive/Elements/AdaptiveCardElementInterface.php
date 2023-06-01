<?php

namespace NotificationChannels\MicrosoftTeams\Cards\Adaptive\Elements;

interface AdaptiveCardElementInterface
{
    /**
     * @param float $version
     * @return array
     */
    public function getContent(float $version): array;
}
