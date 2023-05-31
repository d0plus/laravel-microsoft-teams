<?php

declare(strict_types=1);

namespace NotificationChannels\MicrosoftTeams;

use NotificationChannels\MicrosoftTeams\Cards\Message\Styles;
use NotificationChannels\MicrosoftTeams\Exceptions\CouldNotSendNotification;

/**
 * This is "Office 365 Connector card"
 *
 * @see https://github.com/laravel-notification-channels/microsoft-teams/blob/master/src/MicrosoftTeamsMessage.php
 * @see https://docs.microsoft.com/en-us/microsoftteams/platform/task-modules-and-cards/cards/cards-reference#office-365-connector-card
 * @see https://docs.microsoft.com/en-us/outlook/actionable-messages/message-card-reference
 */
class MessageCard implements MicrosoftTeamsMessageInterface
{
    /** @var array Params payload. */
    protected $payload = [];

    /** @var string webhook url of recipient. */
    protected $webhookUrl = null;

    /**
     * @param string $content
     *
     * @return self
     */
    public static function create(string $content = ''): self
    {
        return new self($content);
    }

    /**
     * Message constructor.
     *
     * @param string $content
     */
    public function __construct(string $content = '')
    {
        $this->payload['@type'] = 'MessageCard';
        $this->payload['@context'] = 'https://schema.org/extensions';
        $this->summary('Incoming Notification')
            ->type(Styles::COLORS_PRIMARY)
            ->content($content);
    }

    /**
     * Set a title.
     *
     * @param string $title - title
     * @param string $sectionId - optional section can be defined (e.g. [$section = '1'].
     *
     * @return MessageCard $this
     */
    public function title(string $title, string $sectionId = null): self
    {
        // if section is defined add it to specified section
        if ($sectionId) {
            $this->payload['sections'][$sectionId]['title'] = $title;
        } else {
            $this->payload['title'] = $title;
            $this->payload['summary'] = $title;
        }
        return $this;
    }

    /**
     * Set a summary.
     *
     * @param string $summary - summary
     *
     * @return MessageCard $this
     */
    public function summary(string $summary): self
    {
        $this->payload['summary'] = $summary;
        return $this;
    }

    /**
     * Type which is used as theme color (any valid hex code).
     *
     * @param string $color - hex color
     *
     * @return MessageCard $this
     */
    public function type(string $color): self
    {
        $this->payload['themeColor'] = $color;
        return $this;
    }

    /**
     * Notification message (Supports Markdown).
     *
     * To add line break in markdown, end the line with 3 spaces + linebreak.
     *
     * @param string $content
     * @param string $sectionId - optional section can be defined (e.g. '1')
     *
     * @return MessageCard $this
     */
    public function content(string $content, string $sectionId = null): self
    {
        // if section is defined add it to specified section
        if ($sectionId) {
            $this->payload['sections'][$sectionId]['text'] = $content;
        } else {
            $this->payload['text'] = $content;
        }
        return $this;
    }

    /**
     * Add an action.
     *
     * @param string $name - name of the action
     * @param string $type - defaults to 'OpenUri' should be one of the following types:
     *  - OpenUri: Opens a URI in a separate browser or app; optionally targets different URIs based on operating systems
     *  - HttpPOST: Sends a POST request to a URL
     *  - ActionCard: Presents one or more input types and associated actions
     *  - InvokeAddInCommand: Opens an Outlook add-in task pane.
     * * @param array $params - optional params (needed for complex types and for section)
     * For more information check out: https://docs.microsoft.com/en-us/outlook/actionable-messages/message-card-reference
     *
     * @return MessageCard $this
     */
    public function action(string $name, $type = 'OpenUri', array $params = []): self
    {
        // fill required values for all types
        $newAction = [
            '@type' => $type,
            'name' => $name,
        ];

        // fill additional params (needed for other types than 'OpenUri')
        if (!empty($params)) {
            $newAction = array_merge($newAction, $params);
        }

        // if section is defined add it to specified section
        if (isset($params['section'])) {
            // remove unsued property from newAction array
            unset($newAction['section']);
            $sectionId = $params['section'];
            $this->payload['sections'][$sectionId]['potentialAction'][] = (object) $newAction;
        } else {
            $this->payload['potentialAction'][] = (object) $newAction;
        }

        return $this;
    }

    /**
     * Add a button.
     * Wrapper for a potential action by just providing $text and $url params.
     *
     * @param string $text - label of the button
     * @param string $url - url to forward to
     * @param array $params - optional params (needed for more complex types  and for section)
     * For more information check out: https://docs.microsoft.com/en-us/outlook/actionable-messages/message-card-reference#openuri-action
     *
     * @return MessageCard $this
     */
    public function button(string $text, string $url = '', array $params = []): self
    {
        // fill targets that is needed for a button
        $newButton = [
            'targets' => [
                (object) [
                    'os' => 'default',
                    'uri' => $url,
                ],
            ],
        ];

        // fill additional params (if any)
        if (!empty($params)) {
            $newButton = array_merge($newButton, $params);
        }

        $this->action($text, 'OpenUri', $newButton);

        return $this;
    }

    /**
     * Add an activity to a section.
     *
     * @param string $activityImage
     * @param string $activityTitle
     * @param string $activitySubtitle
     * @param string $activityText
     * @param string|int $sectionId - in which section to put the property, defaults to standard_section
     *
     * @return $this
     */
    public function activity(string $activityImage = '', string $activityTitle = '', string $activitySubtitle = '', string $activityText = '', $sectionId = 'standard_section'): self
    {
        $this->payload['sections'][$sectionId]['activityImage'] = $activityImage;
        $this->payload['sections'][$sectionId]['activityTitle'] = $activityTitle;
        $this->payload['sections'][$sectionId]['activitySubtitle'] = $activitySubtitle;
        $this->payload['sections'][$sectionId]['activityText'] = $activityText;

        return $this;
    }

    /**
     * Add a fact to a section (Supports Markdown).
     *
     * @param string $name
     * @param string $value
     * @param string|int $sectionId - in which section to put the property, defaults to standard_section
     *
     * @return $this
     */
    public function fact(string $name, string $value, $sectionId = 'standard_section'): self
    {
        $newFact = compact('name', 'value');
        $this->payload['sections'][$sectionId]['facts'][] = $newFact;

        return $this;
    }

    /**
     * Add an image to a section.
     *
     * @param string $imageUri - The URL to the image.
     * @param string $title - A short description of the image. Typically, title is displayed in a tooltip as the user hovers their mouse over the image
     * @param string|int $sectionId - in which section to put the property, defaults to standard_section
     *
     * @return $this
     */
    public function image(string $imageUri, string $title = '', $sectionId = 'standard_section'): self
    {
        $newImage = [
            'image' => $imageUri,
            'title' => $title,
        ];
        $this->payload['sections'][$sectionId]['images'][] = $newImage;

        return $this;
    }

    /**
     * Additional options to pass to message payload object.
     *
     * @param array $options
     * @param string|int $sectionId - optional in which section to put the property
     *
     * @return $this
     */
    public function options(array $options, $sectionId = null): self
    {
        if ($sectionId) {
            $this->payload['sections'][$sectionId] = array_merge($this->payload['sections'][$sectionId], $options);
        }
        $this->payload = array_merge($this->payload, $options);

        return $this;
    }

    /**
     * Recipient's webhook url.
     *
     * @param $webhookUrl - url of webhook
     *
     * @throws CouldNotSendNotification
     *
     * @return $this
     */
    public function to(string $webhookUrl): self
    {
        $this->webhookUrl = $webhookUrl;
        return $this;
    }

    /**
     * Get webhook url.
     *
     * @return string|null $webhookUrl
     */
    public function getWebhookUrl(): ?string
    {
        return $this->webhookUrl;
    }

    /**
     * Get payload value for given key.
     *
     * @param string $key
     *
     * @return mixed|null
     */
    public function getPayloadValue(string $key)
    {
        return $this->payload[$key] ?? null;
    }

    /**
     * Returns params payload.
     *
     * @return array
     */
    public function toArray(): array
    {
        return $this->payload;
    }
}
