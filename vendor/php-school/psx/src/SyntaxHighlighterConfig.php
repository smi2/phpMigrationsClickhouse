<?php

namespace PhpSchool\PSX;

/**
 * Class SyntaxHighlighterConfig
 * @package PhpSchool\PSX
 * @author Aydin Hassan <aydin@hotmail.co.uk>
 */
class SyntaxHighlighterConfig
{
    const TYPE_KEYWORD          = 'keyword';
    const TYPE_BRACE            = 'brace';
    const TYPE_STRING           = 'string';
    const TYPE_CONSTRUCT        = 'construct';
    const TYPE_RETURN_NEW       = 'return_new';
    const TYPE_VAR_DEREF        = 'var_deref';
    const TYPE_CALL_PARENTHESIS = 'call_parenthesis';
    const TYPE_LHS              = 'lhs';
    const TYPE_CLASS            = 'class';
    const TYPE_OPEN_TAG         = 'open_tag';

    /**
     * @var array
     */
    private $types = [
        self::TYPE_KEYWORD,
        self::TYPE_BRACE,
        self::TYPE_STRING,
        self::TYPE_CONSTRUCT,
        self::TYPE_RETURN_NEW ,
        self::TYPE_VAR_DEREF,
        self::TYPE_CALL_PARENTHESIS,
        self::TYPE_LHS,
        self::TYPE_CLASS,
        self::TYPE_OPEN_TAG,
    ];

    /**
     * @var array
     */
    private $colors = [
        self::TYPE_KEYWORD          => Colours::BLUE,
        self::TYPE_BRACE            => Colours::YELLOW,
        self::TYPE_STRING           => Colours::GREEN,
        self::TYPE_CONSTRUCT        => Colours::YELLOW,
        self::TYPE_RETURN_NEW       => Colours::LIGHT_MAGENTA,
        self::TYPE_VAR_DEREF        => Colours::GREEN,
        self::TYPE_CALL_PARENTHESIS => Colours::LIGHT_GRAY,
        self::TYPE_LHS              => Colours::YELLOW,
        self::TYPE_CLASS            => Colours::LIGHT_GRAY,
        self::TYPE_OPEN_TAG         => Colours::CYAN,
    ];

    /**
     * @param array|null $colors
     */
    public function __construct(array $colors = null)
    {
        if (null !== $colors) {
            $types  = array_keys($colors);
            $diff   = array_diff($types, $this->types);
            if (count($diff)) {
                throw new \InvalidArgumentException(sprintf('Types: "%s" are not supported', implode('", "', $diff)));
            }

            $this->colors = array_merge($this->colors, $colors);
        }

        foreach ($this->colors as $colour) {
            if (!defined(Colours::class . "::" . strtoupper($colour))) {
                throw new \InvalidArgumentException(
                    sprintf('Colour: "%s" is not valid. Check: "%s"', $colour, Colours::class)
                );
            }
        }
    }

    /**
     * @param string $type
     *
     * @return string
     */
    public function getColorForType($type)
    {
        // if we don't have a type return default color
        if (!isset($this->colors[$type])) {
            return Colours::DEFAULT_;
        }

        return $this->colors[$type];
    }
}
