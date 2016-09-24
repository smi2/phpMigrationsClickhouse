<?php

namespace PhpSchool\PSX;

use PhpParser\Error;
use PhpParser\Parser;

/**
 * Class SyntaxHighlighter
 * @package PhpSchool\PSX
 * @author Aydin Hassan <aydin@hotmail.co.uk>
 */

class SyntaxHighlighter
{
    /**
     * @var \PhpParser\Parser
     */
    private $parser;

    /**
     * @var SyntaxHighlightPrinter
     */
    private $printer;

    /**
     * @param Parser $parser
     * @param SyntaxHighlightPrinter $printer
     */
    public function __construct(Parser $parser, SyntaxHighlightPrinter $printer)
    {
        $this->parser = $parser;
        $this->printer = $printer;
    }

    /**
     * @param string $code
     * @return string
     */
    public function highlight($code)
    {
        if (!is_string($code)) {
            throw new \InvalidArgumentException('Argument 1 should be a string of valid PHP code');
        }
        try {
            $statements = $this->parser->parse($code);
        } catch (Error $e) {
            throw new \InvalidArgumentException('PHP could not be parsed');
        }

        return $this->printer->prettyPrintFile($statements);
    }
}
