<?php

namespace PhpSchool\PSX;

use PhpParser\PrettyPrinter\Standard;
use PhpParser\Node;
use PhpParser\Node\Scalar;
use PhpParser\Node\Expr;
use PhpParser\Node\Stmt;
use PhpParser\Node\Name;

/**
 * Class SyntaxHighlightPrinter
 * @package PhpSchool\PSX
 * @author Aydin Hassan <aydin@hotmail.co.uk>
 */
class SyntaxHighlightPrinter extends Standard
{

    /**
     * @var SyntaxHighlighterConfig
     */
    private $config;

    /**
     * @var ColourAdapterInterface
     */
    private $colourAdapter;

    /**
     * @param SyntaxHighlighterConfig $config
     * @param ColourAdapterInterface  $colourAdapter
     * @param array                   $options
     */
    public function __construct(
        SyntaxHighlighterConfig $config,
        ColourAdapterInterface $colourAdapter,
        array $options = []
    ) {
        $this->colourAdapter = $colourAdapter;
        parent::__construct($options);
        $this->config = $config;
    }

    /**
     * Pretty prints a file of statements (includes the opening <?php tag if it is required).
     *
     * @param Node[] $stmts Array of statements
     *
     * @return string Pretty printed statements
     */
    public function prettyPrintFile(array $stmts)
    {
        $p = rtrim($this->prettyPrint($stmts));
        $p = preg_replace('/^\?>\n?/', '', $p, -1, $count);
        $p = preg_replace('/<\?php$/', '', $p);
        if (!$count) {
            $p = sprintf("%s\n\n%s", $this->color('<?php', SyntaxHighlighterConfig::TYPE_OPEN_TAG), $p);
        }
        return $p;
    }

    /**
     * @param Stmt\Echo_ $node
     *
     * @return string
     */
    public function pStmt_Echo(Stmt\Echo_ $node)
    {
        return sprintf(
            '%s %s;',
            $this->color('echo', SyntaxHighlighterConfig::TYPE_CONSTRUCT),
            $this->pCommaSeparated($node->exprs)
        );
    }

    /**
     * @param Scalar\String_ $node
     *
     * @return string
     */
    public function pScalar_String(Scalar\String_ $node)
    {
        if ($node->hasAttribute('originalValue')) {
            $string = $node->getAttribute('originalValue');
        } else {
            $string = '\'' . $this->pNoIndent(addcslashes($node->value, '\'\\')) . '\'';
        }
        return $this->color($string, SyntaxHighlighterConfig::TYPE_STRING);
    }

    /**
     * @param Node $node
     *
     * @return string
     */
    protected function pCallLhs(Node $node)
    {
        if ($node instanceof Name
            || $node instanceof Expr\Variable
            || $node instanceof Expr\ArrayDimFetch
            || $node instanceof Expr\FuncCall
            || $node instanceof Expr\MethodCall
            || $node instanceof Expr\StaticCall
            || $node instanceof Expr\Array_
        ) {
            return $this->color($this->p($node), SyntaxHighlighterConfig::TYPE_LHS);
        } else {
            return '(' . $this->p($node) . ')';
        }
    }

    /**
     * @param Node $node
     *
     * @return string
     */
    protected function pDereferenceLhs(Node $node)
    {
        if ($node instanceof Expr\Variable
            || $node instanceof Name
            || $node instanceof Expr\ArrayDimFetch
            || $node instanceof Expr\PropertyFetch
            || $node instanceof Expr\StaticPropertyFetch
            || $node instanceof Expr\FuncCall
            || $node instanceof Expr\MethodCall
            || $node instanceof Expr\StaticCall
            || $node instanceof Expr\Array_
            || $node instanceof Scalar\String_
            || $node instanceof Expr\ConstFetch
            || $node instanceof Expr\ClassConstFetch
        ) {
            return $this->color($this->p($node), SyntaxHighlighterConfig::TYPE_LHS);
        } else {
            return '(' . $this->p($node) . ')';
        }
    }

    /**
     * @param Stmt\Return_ $node
     *
     * @return string
     */
    public function pStmt_Return(Stmt\Return_ $node)
    {
        return sprintf(
            '%s%s;',
            $this->color('return', SyntaxHighlighterConfig::TYPE_RETURN_NEW),
            (null !== $node->expr ? ' ' . $this->p($node->expr) : '')
        );
    }

    // Control flow

    /**
     * @param Stmt\If_ $node
     *
     * @return string
     */
    public function pStmt_If(Stmt\If_ $node)
    {
        return sprintf(
            "%s (%s) %s%s\n%s%s%s",
            $this->color('if', SyntaxHighlighterConfig::TYPE_KEYWORD),
            $this->p($node->cond),
            $this->color('{', SyntaxHighlighterConfig::TYPE_BRACE),
            $this->pStmts($node->stmts),
            $this->color('}', SyntaxHighlighterConfig::TYPE_BRACE),
            $this->pImplode($node->elseifs),
            (null !== $node->else ? $this->p($node->else) : '')
        );
    }

    /**
     * @param Stmt\ElseIf_ $node
     *
     * @return string
     */
    public function pStmt_ElseIf(Stmt\ElseIf_ $node)
    {
        return sprintf(
            " %s (%s) %s%s\n%s",
            $this->color('elseif', SyntaxHighlighterConfig::TYPE_KEYWORD),
            $this->p($node->cond),
            $this->color('{', SyntaxHighlighterConfig::TYPE_BRACE),
            $this->pStmts($node->stmts),
            $this->color('}', SyntaxHighlighterConfig::TYPE_BRACE)
        );
    }

    /**
     * @param Stmt\For_ $node
     *
     * @return string
     */
    public function pStmt_For(Stmt\For_ $node)
    {
        return sprintf(
            "%s (%s;%s%s;%s%s) %s%s\n%s",
            $this->color('for', SyntaxHighlighterConfig::TYPE_KEYWORD),
            $this->pCommaSeparated($node->init),
            (!empty($node->cond) ? ' ' : ''),
            $this->pCommaSeparated($node->cond),
            (!empty($node->loop) ? ' ' : ''),
            $this->pCommaSeparated($node->loop),
            $this->color('{', SyntaxHighlighterConfig::TYPE_BRACE),
            $this->pStmts($node->stmts),
            $this->color('}', SyntaxHighlighterConfig::TYPE_BRACE)
        );
    }

    /**
     * @param Stmt\Foreach_ $node
     *
     * @return string
     */
    public function pStmt_Foreach(Stmt\Foreach_ $node)
    {
        return sprintf(
            "%s (%s as %s%s%s) %s%s\n%s",
            $this->color('foreach', SyntaxHighlighterConfig::TYPE_KEYWORD),
            $this->p($node->expr),
            (null !== $node->keyVar ? $this->p($node->keyVar) . ' => ' : ''),
            ($node->byRef ? '&' : ''),
            $this->p($node->valueVar),
            $this->color('{', SyntaxHighlighterConfig::TYPE_BRACE),
            $this->pStmts($node->stmts),
            $this->color('}', SyntaxHighlighterConfig::TYPE_BRACE)
        );
    }

    /**
     * @param Stmt\While_ $node
     *
     * @return string
     */
    public function pStmt_While(Stmt\While_ $node)
    {
        return sprintf(
            "%s (%s) %s%s\n%s",
            $this->color('while', SyntaxHighlighterConfig::TYPE_KEYWORD),
            $this->p($node->cond),
            $this->color('{', SyntaxHighlighterConfig::TYPE_BRACE),
            $this->pStmts($node->stmts),
            $this->color('}', SyntaxHighlighterConfig::TYPE_BRACE)
        );
    }

    /**
     * @param Stmt\Do_ $node
     *
     * @return string
     */
    public function pStmt_Do(Stmt\Do_ $node)
    {
        return sprintf(
            "%s %s%s \n%s %s (%s);",
            $this->color('do', SyntaxHighlighterConfig::TYPE_KEYWORD),
            $this->color('{', SyntaxHighlighterConfig::TYPE_BRACE),
            $this->pStmts($node->stmts),
            $this->color('}', SyntaxHighlighterConfig::TYPE_BRACE),
            $this->color('while', SyntaxHighlighterConfig::TYPE_KEYWORD),
            $this->p($node->cond)
        );
    }

    /**
     * @param Stmt\Switch_ $node
     *
     * @return string
     */
    public function pStmt_Switch(Stmt\Switch_ $node)
    {
        return sprintf(
            "%s (%s) %s%s\n%s",
            $this->color('switch', SyntaxHighlighterConfig::TYPE_KEYWORD),
            $this->p($node->cond),
            $this->color('{', SyntaxHighlighterConfig::TYPE_BRACE),
            $this->pStmts($node->cases),
            $this->color('}', SyntaxHighlighterConfig::TYPE_BRACE)
        );
    }

    /**
     * @param Stmt\Case_ $node
     *
     * @return string
     */
    public function pStmt_Case(Stmt\Case_ $node)
    {
        return sprintf(
            "%s:%s",
            (null !== $node->cond ? 'case ' . $this->p($node->cond) : 'default'),
            $this->pStmts($node->stmts)
        );
    }

    /**
     * @param Stmt\TryCatch $node
     *
     * @return string
     */
    public function pStmt_TryCatch(Stmt\TryCatch $node)
    {
        if ($node->finallyStmts !== null) {
            $finallyStatement = sprintf(
                " %s %s%s\n%s",
                $this->color('finally', SyntaxHighlighterConfig::TYPE_KEYWORD),
                $this->color('{', SyntaxHighlighterConfig::TYPE_BRACE),
                $this->pStmts($node->finallyStmts),
                $this->color('}', SyntaxHighlighterConfig::TYPE_BRACE)
            );
        } else {
            $finallyStatement = '';
        }

        return sprintf(
            "%s %s %s\n%s%s%s",
            $this->color('try', SyntaxHighlighterConfig::TYPE_KEYWORD),
            $this->color('{', SyntaxHighlighterConfig::TYPE_BRACE),
            $this->pStmts($node->stmts),
            $this->color('}', SyntaxHighlighterConfig::TYPE_BRACE),
            $this->pImplode($node->catches),
            $finallyStatement
        );
    }

    /**
     * @param Stmt\Catch_ $node
     *
     * @return string
     */
    public function pStmt_Catch(Stmt\Catch_ $node)
    {
        return sprintf(
            " %s (%s $%s) %s%s\n%s",
            $this->color('catch', SyntaxHighlighterConfig::TYPE_KEYWORD),
            $this->p($node->type),
            $node->var,
            $this->color('{', SyntaxHighlighterConfig::TYPE_BRACE),
            $this->pStmts($node->stmts),
            $this->color('}', SyntaxHighlighterConfig::TYPE_BRACE)
        );
    }

    /**
     * @param Stmt\Break_ $node
     *
     * @return string
     */
    public function pStmt_Break(Stmt\Break_ $node)
    {
        return sprintf(
            '%s%s;',
            $this->color('break', SyntaxHighlighterConfig::TYPE_KEYWORD),
            ($node->num !== null ? ' ' . $this->p($node->num) : '')
        );
    }

    /**
     * @param Stmt\Continue_ $node
     *
     * @return string
     */
    public function pStmt_Continue(Stmt\Continue_ $node)
    {
        return sprintf(
            '%s%s;',
            $this->color('continue', SyntaxHighlighterConfig::TYPE_KEYWORD),
            ($node->num !== null ? ' ' . $this->p($node->num) : '')
        );
    }

    /**
     * @param Stmt\Throw_ $node
     *
     * @return string
     */
    public function pStmt_Throw(Stmt\Throw_ $node)
    {
        return sprintf(
            '%s %s;',
            $this->color('throw', SyntaxHighlighterConfig::TYPE_KEYWORD),
            $this->p($node->expr)
        );
    }

    /**
     * @param Stmt\Goto_ $node
     *
     * @return string
     */
    public function pStmt_Goto(Stmt\Goto_ $node)
    {
        return sprintf(
            '%s %s;',
            $this->color('goto', SyntaxHighlighterConfig::TYPE_KEYWORD),
            $node->name
        );
    }

    //Other

    /**
     * @param Expr\Closure $node
     *
     * @return string
     */
    public function pExpr_Closure(Expr\Closure $node)
    {
        return sprintf(
            "%s%s %s(%s)%s%s %s%s\n%s",
            ($node->static ? 'static ' : ''),
            $this->color('function', SyntaxHighlighterConfig::TYPE_KEYWORD),
            ($node->byRef ? '&' : ''),
            $this->pCommaSeparated($node->params),
            (!empty($node->uses) ? ' use(' . $this->pCommaSeparated($node->uses) . ')': ''),
            (null !== $node->returnType ? ' : ' . $this->pType($node->returnType) : ''),
            $this->color('{', SyntaxHighlighterConfig::TYPE_BRACE),
            $this->pStmts($node->stmts),
            $this->color('}', SyntaxHighlighterConfig::TYPE_BRACE)
        );
    }

    /**
     * @param Stmt\Else_ $node
     *
     * @return string
     */
    public function pStmt_Else(Stmt\Else_ $node)
    {
        return sprintf(
            " %s %s%s\n%s",
            $this->color('else', SyntaxHighlighterConfig::TYPE_KEYWORD),
            $this->color('{', SyntaxHighlighterConfig::TYPE_BRACE),
            $this->pStmts($node->stmts),
            $this->color('}', SyntaxHighlighterConfig::TYPE_BRACE)
        );
    }

    /**
     * @param Expr\FuncCall $node
     *
     * @return string
     */
    public function pExpr_FuncCall(Expr\FuncCall $node)
    {
        return sprintf(
            '%s%s%s%s',
            $this->pCallLhs($node->name),
            $this->color('(', SyntaxHighlighterConfig::TYPE_CALL_PARENTHESIS),
            $this->pCommaSeparated($node->args),
            $this->color(')', SyntaxHighlighterConfig::TYPE_CALL_PARENTHESIS)
        );
    }

    /**
     * @param Expr\MethodCall $node
     *
     * @return string
     */
    public function pExpr_MethodCall(Expr\MethodCall $node)
    {
        return sprintf(
            '%s%s%s%s%s%s',
            $this->pDereferenceLhs($node->var),
            $this->color('->', SyntaxHighlighterConfig::TYPE_VAR_DEREF),
            $this->pObjectProperty($node->name),
            $this->color('(', SyntaxHighlighterConfig::TYPE_CALL_PARENTHESIS),
            $this->pCommaSeparated($node->args),
            $this->color(')', SyntaxHighlighterConfig::TYPE_CALL_PARENTHESIS)
        );
    }

    /**
     * @param Expr\New_ $node
     *
     * @return string
     */
    public function pExpr_New(Expr\New_ $node)
    {
        if ($node->class instanceof Stmt\Class_) {
            $args = $node->args ? '(' . $this->pCommaSeparated($node->args) . ')' : '';
            return 'new ' . $this->pClassCommon($node->class, $args);
        }
        return sprintf(
            '%s %s%s%s%s',
            $this->color('new', SyntaxHighlighterConfig::TYPE_RETURN_NEW),
            $this->color($this->p($node->class), SyntaxHighlighterConfig::TYPE_CLASS),
            $this->color('(', SyntaxHighlighterConfig::TYPE_CALL_PARENTHESIS),
            $this->pCommaSeparated($node->args),
            $this->color(')', SyntaxHighlighterConfig::TYPE_CALL_PARENTHESIS)
        );
    }

    /**
     * @param Expr\Array_ $node
     * @return string
     */
    public function pExpr_Array(Expr\Array_ $node)
    {
        if ($this->options['shortArraySyntax'] || !$node->hasAttribute('traditionalArray')) {
            return '[' . $this->pArrayList($node, $node->items) . ']';
        } else {
            return 'array(' . $this->pArrayList($node, $node->items) . ')';
        }
    }

    /**
     * @param Expr\Array_ $parent
     * @param Expr\ArrayItem[] $nodes
     * @return string
     */
    public function pArrayList(Expr\Array_ $parent, array $nodes)
    {
        $lineNumbers = [$parent->getAttribute('startLine', null)];
        foreach ($nodes as $node) {
            $lineNumbers[] = $node->getAttribute('startLine', null);
        }
        
        //all the same line
        if (count(array_unique($lineNumbers)) == 1) {
            return $this->pCommaSeparated($nodes);
        }
        
        $output = "\n";
        foreach ($nodes as $key => $node) {
            $output .= sprintf("    %s,\n", $this->p($node));
        }
        return $output;
    }

    /**
     * @param Expr\Exit_ $node
     * @return string
     */
    public function pExpr_Exit(Expr\Exit_ $node)
    {
        $construct = 'exit';
        if ($node->getAttribute('isDie', false)) {
            $construct = 'die';
        }
        
        return $construct . (null !== $node->expr ? '(' . $this->p($node->expr) . ')' : '');
    }

    /**
     * @param string $string
     * @param string $type
     *
     * @return string
     */
    protected function color($string, $type)
    {
        return $this->colourAdapter->colour(
            $string,
            $this->config->getColorForType($type)
        );
    }
}
