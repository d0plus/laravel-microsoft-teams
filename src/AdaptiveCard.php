<?php

namespace NotificationChannels\MicrosoftTeams;

use NotificationChannels\MicrosoftTeams\Cards\Adaptive\Actions\AdaptiveCardActionInterface;
use NotificationChannels\MicrosoftTeams\Cards\Adaptive\Actions\OpenUrl;
use NotificationChannels\MicrosoftTeams\Cards\Adaptive\Elements\AdaptiveCardElementInterface;
use NotificationChannels\MicrosoftTeams\Cards\Adaptive\Elements\Fact;
use NotificationChannels\MicrosoftTeams\Cards\Adaptive\Elements\FactSet;
use NotificationChannels\MicrosoftTeams\Cards\Adaptive\Elements\Image;
use NotificationChannels\MicrosoftTeams\Cards\Adaptive\Elements\TextBlock;
use NotificationChannels\MicrosoftTeams\Cards\Adaptive\Styles;

use NotificationChannels\MicrosoftTeams\Exceptions\CouldNotSendNotification;

/**
 * This is "Adaptive Card"
 * @see https://docs.microsoft.com/en-us/adaptive-cards/authoring-cards/getting-started
 * @see https://adaptivecards.io/explorer/
 *
 * Some logic taken from:
 * @see https://github.com/sebbmeyer/php-microsoft-teams-connector/blob/master/src/Cards/Adaptive/CustomAdaptiveCard.php
 */
class AdaptiveCard
{
    /** @var string webhook url of recipient. */
    protected $webhookUrl = null;

    /**
     * The body of the card is made up of building-blocks known as elements.
     * "Elements" can be composed in nearly infinite arrangements to create many types of cards
     * Type: Array of Elements
     * @var array
     */
    protected $body;

    /**
     * Many cards have a set of actions a user may take on it.
     * This property describes those actions
     * which typically get rendered in an "action bar" at the bottom.
     * Type: Array of action objects
     * @var array
     */
    protected $actions;

    /**
     * Config for MS Teams nmentions.
     * @var array
     */
    protected $mentions;

    /**
     * Supported versions of card
     */
    protected const SUPPORTED_VERSIONS = [1.0, 1.1, 1.2];

    /**
     * @param string $content
     *
     * @return self
     */
    public static function create(string $content = '', $version = 1.2): self
    {
        return new self($content);
    }

    /**
     * Message constructor.
     *
     * @param string $content
     */
    public function __construct(string $content = '', $version = 1.2)
    {
        $this->version = (in_array($version, self::SUPPORTED_VERSIONS)) ? $version : 1.2;
        if ($content) {
            $this->text($content);
        }
    }

    /**
     * Adds single element to card body
     *
     * @param AdaptiveCardElement $element
     * @return $this
     */
    public function addElement(AdaptiveCardElementInterface $element): self
    {
        if (!isset($this->body)) {
            $this->body = [];
        }
        $this->body[] = $element->getContent($this->version);
        return $this;
    }

    /**
     * Adds single action to card actions
     *
     * @param AdaptiveCardActionInterface $action
     * @return $this
     */
    public function addAction(AdaptiveCardActionInterface $action): self
    {
        if (!isset($this->actions)) {
            $this->actions = [];
        }
        $this->actions[] = $action->getContent($this->version);
        return $this;
    }

    /**
     * Common title block
     * @param string $text
     * @return $this
     */
    public function title(string $text): self
    {
        $textBlock = (new TextBlock($text))
            ->setSize(Styles::FONT_SIZE_LARGE)
            ->setWeight(Styles::FONT_WEIGHT_BOLDER)
            ->setWrap(true)
            ->setIsHeading();
        $this->addElement($textBlock);
        return $this;
    }

    /**
     * Common text block
     * @param string $text
     * @return $this
     */
    public function text(string $text): self
    {
        $textBlock = (new TextBlock($text))
            ->setSize(Styles::FONT_SIZE_MEDIUM)
            ->setWrap(true);
        $this->addElement($textBlock);
        return $this;
    }

    /**
     * Add a button.
     * Wrapper for a potential action by just providing $text and $url params.
     *
     * @param string $text - label of the button
     * @param string $url - url to forward to
     * @return $this
     */
    public function button(string $text, string $url): self
    {
        $openUrl = (new OpenUrl($url))->setTitle($text);
        $this->addAction($openUrl);
        return $this;
    }

    /**
     * Common image block
     * @param string $url
     * @return $this
     */
    public function image(string $url): self
    {
        $this->addElement(new Image($url));
        return $this;
    }

    /**
     * Add a single fact in set.
     *
     * Use it, if only one fact required
     *
     * @param string $name
     * @param string $value
     *
     * @return $this
     */
    public function fact(string $name, string $value): self
    {
        $fact = Fact::create($name, $value);
        $factSet = (new FactSet([$fact]));
        $this->addElement($factSet);
        return $this;
    }

    /**
     * Add set of facts
     * @param array $facts
     *
     * @return $this
     */
    public function factSet(array $facts): self
    {
        $this->addElement(new FactSet($facts));
        return $this;
    }

    /**
     * Simple Fact set creation via assotiative array
     * Please note - if array is not associative - fact title would be set as empty string
     * @param array $items - must be array withs values that castable to string
     *
     * @return $this
     */
    public function facts(array $items): self
    {
        if (empty($items)){
            return $this;
        }
        return $this->factSet($this->convertArrayToFacts($items));
    }

    /**
     * Conert array to facts array
     * @param array $items - must be array withs values that castable to string
     * @return array
     */
    protected function convertArrayToFacts(array $items): array
    {
        $facts = [];
        foreach ($items as $key => $value) {
            //Check for associative array. If not - do not display numeric index as title
            $facts[] = Fact::create(is_string($key) ? $key : '', (string) $value);
        }
        return $facts;
    }

    /**
     * Structure similar to attachments in Slack
     * There is pre text and one of few facts
     * @param string $text - pretend text for fact group
     * @param array $items - must be array withs values that castable to string
     * @param string $color - pretend text color
     * @return $this
     */
    public function attachements(string $text, array $items, string $color = Styles::COLORS_DEFAULT)
    {
        $textBlock = (new TextBlock($text))
            ->setSize(Styles::FONT_SIZE_MEDIUM)
            ->setWeight(Styles::FONT_WEIGHT_BOLDER)
            ->setColor($color)
            ->setWrap(true);

        $facts = $this->convertArrayToFacts($items);
        $factSet = (new FactSet($facts))->setSpacing(Styles::SPACING_NONE);

        $this->addElement($textBlock)->addElement($factSet);
        return $this;
    }


     /**
     * Simple list
     * @see https://docs.microsoft.com/en-us/microsoftteams/platform/task-modules-and-cards/cards/cards-format?tabs=adaptive-md%2Cconnector-html#user-mention-in-incoming-webhook-with-adaptive-cards
     * @param array $items - must be array withs values that castable to string
     *
     * @return $this
     */
    public function list(array $items)
    {
        //- Item 1\r- Item 2\r- Item 3
        $list = '';
        foreach ($items as $item) {
            $list .= '- ' . (string) $item . PHP_EOL;
        }
        $this->text($list);
        return $this;
    }

    /**
     * Add mention to user
     *
     * In this case <at>' . $name . '</at> should be in other text block
     *
     * Note: if text do not contain `<at>' . $name . '</at>` - 400 error returned
     * @see https://docs.microsoft.com/en-us/microsoftteams/platform/task-modules-and-cards/cards/cards-format?tabs=adaptive-md%2Cconnector-html#user-mention-in-incoming-webhook-with-adaptive-cards
     * @param string $id
     * @param string $name
     * @return $this
     */
    public function mention(string $id, string $name): self
    {
        $mention = [
            'type' => 'mention',
            'text' => '<at>' . $name . '</at>',
            'mentioned' => [
                'id' => $id,
                'name' => $name
            ]
        ];
        if (!isset($this->mentions)) {
            $this->mentions = [];
        }
        $this->mentions[] = $mention;
        return $this;
    }

    /**
     * Add mention to user as separate line of text.
     *
     * @param string $id
     * @param string $name
     * @return $this
     */
    public function textMention(string $id, string $name): self
    {
        $textBlock = (new TextBlock('<at>' . $name . '</at>'))
            ->setSize(Styles::FONT_SIZE_MEDIUM)
            ->setWrap(true)
            ->setSpacing(Styles::SPACING_NONE);
        $this->addElement($textBlock);
        $this->mention($id, $name);
        return $this;
    }

    /**
     * Multiple mentions of users in one row.
     *
     * @param array $items - should be an associative array with key: MicrosoftTeamsMentionId and value - any display name
     * @return self
     */
    public function textMentions(array $items): self
    {
        $text = [];
        foreach ($items as $id => $name) {
            if (!is_string($id) || !$name) {
                continue;
            }
            $text [] = '<at>' . $name . '</at>';
            $this->mention($id, $name);
        }
        $this->text(implode(', ', $text));
        return $this;
    }

    /**
     * Additional options to pass to message payload object.
     *
     * @param array $options
     * @param string|int $sectionId - optional in which section to put the property
     *
     * @return MicrosoftTeamsMessage $this
     */
    public function options(array $options, $sectionId = null): self
    {

        return $this;
    }

    /**
     * Recipient's webhook url.
     *
     * @param $webhookUrl - url of webhook
     *
     * @throws CouldNotSendNotification
     *
     * @return MicrosoftTeamsMessage $this
     */
    public function to(?string $webhookUrl): self
    {
        if (! $webhookUrl) {
            throw CouldNotSendNotification::microsoftTeamsWebhookUrlMissing();
        }
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
     * Determine if webhook url is not given.
     *
     * @return bool
     */
    public function toNotGiven(): bool
    {
        return ! $this->webhookUrl;
    }


    /**
     * Returns params content.
     *
     * MS Teams content width:
     * @see https://docs.microsoft.com/en-us/microsoftteams/platform/task-modules-and-cards/cards/cards-format?tabs=adaptive-md%2Cconnector-html#full-width-adaptive-card
     *
     * @return array
     */
    public function toArray(): array
    {
        $card = [
            'contentType' => 'application/vnd.microsoft.card.adaptive',
            'contentUrl' => null,
            'content' => [
                '$schema' => 'http://adaptivecards.io/schemas/adaptive-card.json',
                'type' => 'AdaptiveCard',
                'version' => $this->version,
                'msteams' => [
                    'width' => 'full'
                ]
            ],
        ];

        if (isset($this->body)) {
            $card['content']['body'] = $this->body;
        }
        if (isset($this->actions)) {
            $card['content']['actions'] = $this->actions;
        }
        if (isset($this->mentions)) {
            $card['content']['msteams']['entities'] = $this->mentions;
        }

        return [
            'type' => 'message',
            'attachments' => [$card],
        ];
    }
}
