<?php

namespace PhpSchool\PSX;

/**
 * Interface ColourAdapterInterface
 * @package PhpSchool\PSX
 * @author Aydin Hassan <aydin@hotmail.co.uk>
 */
interface ColourAdapterInterface
{
    /**
     * Color the string if possible,
     * if not - just return the string
     *
     * @param string $string
     * @param string $colour
     *
     * @return string
     */
    public function colour($string, $colour);
}
