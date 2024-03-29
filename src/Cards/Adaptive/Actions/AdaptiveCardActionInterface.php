<?php

namespace NotificationChannels\MicrosoftTeams\Cards\Adaptive\Actions;

/**
 * @see https://github.com/sebbmeyer/php-microsoft-teams-connector/blob/master/src/Cards/Adaptive/Actions/AdaptiveCardAction.php
 */
interface AdaptiveCardActionInterface
{
    /**
     * @param float $version
     * @return array
     */
    public function getContent(float $version): array;
}
