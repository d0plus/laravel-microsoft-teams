<?php

declare(strict_types=1);

namespace NotificationChannels\MicrosoftTeams\Cards\Adaptive\Elements;

use Exception;

/**
 * Base element
 * @see https://github.com/sebbmeyer/php-microsoft-teams-connector/blob/master/src/Cards/Adaptive/Elements/BaseElement.php
 */
class BaseElement
{
    /**
     * Type of element.
     * Required: yes
     * @version 1.0
     * @var string
     */
    private $type;

    /**
     * Specifies the height of the element.
     * Type: BlockElementHeight
     * Required: no
     * @version 1.1
     * @var string
     */
    private $height;

    /**
     * When true, draw a separating line at the top of the element.
     * Default: false
     * Required: no
     * @version 1.0
     * @var boolean
     */
    private $separator;

    /**
     * Controls the amount of spacing between this element and the preceding element.
     * Type: Spacing
     * Required: no
     * @version 1.0
     * @var string
     */
    private $spacing;

    /**
     * A unique identifier associated with the item.
     * Required: no
     * @version 1.0
     * @var string
     */
    private $id;

    /**
     * If false, this item will be removed from the visual tree.
     * Default: true
     * Required: no
     * @version 1.2
     * @var boolean
     */
    private $isVisible;

    /**
     * Adds base properties to given element and returns it
     * @param  array  $element
     * @param  float  $version
     * @return array
     */
    public function getBaseContent(array $element, $version): array
    {
        // if type is not set, throw exception
        if (!isset($this->type)) {
            throw new Exception("Card element type is not set", 500);
        }
        $element["type"] = $this->type;

        if (isset($this->height) && $version >= 1.1) {
            $element["height"] = $this->height;
        }
        if (isset($this->separator) && $version >= 1.0) {
            $element["separator"] = $this->separator;
        }
        if (isset($this->spacing) && $version >= 1.0) {
            $element["spacing"] = $this->spacing;
        }
        if (isset($this->id) && $version >= 1.0) {
            $element["id"] = $this->id;
        }
        if (isset($this->isVisible) && $version >= 1.2) {
            $element["isVisible"] = $this->isVisible;
        }

        return $element;
    }

    /**
     * Sets type of element
     * @param string $type
     * @return BaseElement
     */
    public function setType(string $type)
    {
        $this->type = $type;
        return $this;
    }

    /**
     * Sets height. Available options can be found in Styles.php
     * @param string $height
     * @return BaseElement
     */
    public function setHeight(string $height)
    {
        $this->height = $height;
        return $this;
    }

    /**
     * Sets separator flag
     * @param bool $separator
     * @return BaseElement
     */
    public function setSeparator(bool $separator)
    {
        $this->separator = $separator;
        return $this;
    }

    /**
     * Sets spacing. Available options can be found in Styles.php
     * @param string $spacing
     * @return BaseElement
     */
    public function setSpacing(string $spacing)
    {
        $this->spacing = $spacing;
        return $this;
    }

    /**
     * Sets id
     * @param string $id
     * @return BaseElement
     */
    public function setId(string $id)
    {
        $this->id = $id;
        return $this;
    }

    /**
     * Sets isVisible flag
     * @param bool $isVisible
     * @return BaseElement
     */
    public function setIsVisible(bool $isVisible)
    {
        $this->isVisible = $isVisible;
        return $this;
    }
}
