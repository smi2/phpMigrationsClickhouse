<?php

namespace PhpSchool\PSX;

use PhpParser\Lexer\Emulative;
use PhpParser\Parser\Tokens;

/**
 * Class Lexer
 * @package PhpSchool\PSX
 * @author Aydin Hassan <aydin@hotmail.co.uk>
 */
class Lexer extends Emulative
{
    public function getNextToken(&$value = null, &$startAttributes = null, &$endAttributes = null)
    {
        $tokenId = parent::getNextToken($value, $startAttributes, $endAttributes);

        if ($tokenId == Tokens::T_ARRAY) {
            $startAttributes['traditionalArray'] = true;
        }

        if ($tokenId == Tokens::T_EXIT) {
            $startAttributes['isDie'] = strtolower($value) === 'die';
        }

        if ($tokenId == Tokens::T_CONSTANT_ENCAPSED_STRING) {
            $endAttributes['originalValue'] = $value;
        }
        
        return $tokenId;
    }
}
