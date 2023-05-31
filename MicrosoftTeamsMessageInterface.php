<?php

declare(strict_types=1);

namespace common\components\MicrosoftTeams;

/**
 * @see interface for cards https://docs.microsoft.com/en-us/microsoftteams/platform/task-modules-and-cards/cards/cards-reference#card-types
 */
interface MicrosoftTeamsMessageInterface
{
    /**
     * Recipient's webhook url.
     *
     * @param $webhookUrl - url of webhook
     *
     * @return $this
     */
    public function to(string $webhookUrl);

    /**
     * Get webhook url.
     *
     * @return string|null $webhookUrl
     */
    public function getWebhookUrl(): ?string;

    /**
     * Returns params payload.
     *
     * @return array
     */
    public function toArray(): array;
}
