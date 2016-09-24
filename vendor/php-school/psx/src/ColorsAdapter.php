<?php

namespace PhpSchool\PSX;

use Colors\Color;
use Colors\NoStyleFoundException;

/**
 * Class ColorsAdapter
 * @package PhpSchool\PSX
 * @author Aydin Hassan <aydin@hotmail.co.uk>
 */
class ColorsAdapter implements ColourAdapterInterface
{
    /**
     * @var Color
     */
    private $color;

    /**
     * @param Color $color
     */
    public function __construct(Color $color)
    {
        $this->color = $color;
    }

    /**
     * @param string $string
     * @param string $colour
     *
     * @return string
     */
    public function colour($string, $colour)
    {
        try {
            return $this->color->__invoke($string)
                ->apply($colour)
                ->__toString();
        } catch (NoStyleFoundException $e) {
            return $string;
        }
    }
}
