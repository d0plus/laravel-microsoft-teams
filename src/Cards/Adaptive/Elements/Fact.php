<?php

namespace NotificationChannels\MicrosoftTeams\Cards\Adaptive\Elements;

/**
 * Fact
 *
 * @version >= 1.0
 * @see https://adaptivecards.io/explorer/Fact.html
 * @see https://github.com/sebbmeyer/php-microsoft-teams-connector/blob/master/src/Cards/Adaptive/Elements/Fact.php
 */
class Fact
{
    /**
     * The title of the fact.
     * Required: yes
     * @version 1.0
     * @var string
     */
    private $title;

    /**
     * The value of the fact.
     * Required: yes
     * @version 1.0
     * @var string
     */
    private $value;

     /**
     * @param string $title
     * @param string $value
     *
     * @return Fact
     */
    public static function create(string $title, string $value): self
    {
        return new self($title, $value);
    }

    /**
     * @param string $title
     * @param string $value
     */
    public function __construct(string $title,string $value)
    {
        $this->title = $title;
        $this->value = $value;
    }

    /**
     * Return fact content
     * @return array
     */
    public function getContent(): array
    {
        return [
            'title' => $this->title,
            'value' => $this->value,
        ];
    }
}
