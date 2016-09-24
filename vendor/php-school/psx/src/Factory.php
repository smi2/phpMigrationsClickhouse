<?php

namespace PhpSchool\PSX;

use PhpParser\ParserFactory;
use Colors\Color;

/**
 * Class Factory
 * @package PhpSchool\PSX
 * @author Aydin Hassan <aydin@hotmail.co.uk>
 */
class Factory
{
    /**
     * @return SyntaxHighlighter
     */
    public function __invoke()
    {
        $lexer = new Lexer([
            'usedAttributes' => [
                'comments', 'startLine', 'endLine', 'startFilePos', 'endFilePos', 'startTokenPos', 'endTokenPos'
            ]
        ]);
        
        $parserFactory = new ParserFactory;
        $color = new Color;
        $color->setForceStyle(true);
        return new SyntaxHighlighter(
            $parserFactory->create(ParserFactory::PREFER_PHP7, $lexer),
            new SyntaxHighlightPrinter(
                new SyntaxHighlighterConfig,
                new ColorsAdapter($color)
            )
        );
    }
}
