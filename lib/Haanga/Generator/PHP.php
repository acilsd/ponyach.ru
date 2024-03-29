<?php
/*
  +---------------------------------------------------------------------------------+
  | Copyright (c) 2010 César Rodas and Menéame Comunicacions S.L.                   |
  +---------------------------------------------------------------------------------+
  | Redistribution and use in source and binary forms, with or without              |
  | modification, are permitted provided that the following conditions are met:     |
  | 1. Redistributions of source code must retain the above copyright               |
  |    notice, this list of conditions and the following disclaimer.                |
  |                                                                                 |
  | 2. Redistributions in binary form must reproduce the above copyright            |
  |    notice, this list of conditions and the following disclaimer in the          |
  |    documentation and/or other materials provided with the distribution.         |
  |                                                                                 |
  | 3. All advertising materials mentioning features or use of this software        |
  |    must display the following acknowledgement:                                  |
  |    This product includes software developed by César D. Rodas.                  |
  |                                                                                 |
  | 4. Neither the name of the César D. Rodas nor the                               |
  |    names of its contributors may be used to endorse or promote products         |
  |    derived from this software without specific prior written permission.        |
  |                                                                                 |
  | THIS SOFTWARE IS PROVIDED BY CÉSAR D. RODAS ''AS IS'' AND ANY                   |
  | EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE IMPLIED       |
  | WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE          |
  | DISCLAIMED. IN NO EVENT SHALL CÉSAR D. RODAS BE LIABLE FOR ANY                  |
  | DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES      |
  | (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES;    |
  | LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND     |
  | ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT      |
  | (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE OF THIS   |
  | SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE                     |
  +---------------------------------------------------------------------------------+
  | Authors: César Rodas <crodas@php.net>                                           |
  +---------------------------------------------------------------------------------+
*/

// addslashes_ex($string) {{{
/**
 *  addslashes like function for single quote string ('foo')
 *
 *  @return string
 */
function addslashes_ex($string)
{
    return str_replace(array("\\", "'"), array("\\\\", "\\'"), $string);
}
// }}}

/**
 *  Haanga_Generator_PHP class
 *
 *  This class takes the generated AST structure (arrays), 
 *  and generated the PHP represantion.
 *
 *
 */
class Haanga_Generator_PHP
{
    protected $ident;
    protected $tab = "    ";
    protected $scopeVariableName;

    // getCode (AST $op_code) {{{
    /**
     *  Transform the AST generated by the Haanga_Compiler class
     *  and return the equivalent PHP code.
     *
     *  @param array $op_code
     *  
     *  @return string
     */
    final function getCode($op_code, $scope)
    {
        $this->scopeVariableName = $scope;
        $this->ident = 0;
        $code = "";
        $size = count($op_code);
        for ($i=0; $i < $size; $i++) {
            $op = $op_code[$i];
            if (!isset($op['op'])) {
                throw new Haanga_Compiler_Exception("Invalid \$op_code ".print_r($op, TRUE));
            }

            /* echo optimization {{{ */
            if ($op['op'] == 'print') {
                do {
                    $next_op = $op_code[$i+1];
                    if (!isset($next_op) || $next_op['op'] != 'print') {
                        break;
                    }
                    for ($e=0; $e < count($next_op); $e++) {
                        if (!isset($next_op[$e])) {
                            break;
                        }
                        $op[] = $next_op[$e];
                    }
                    $i++;
                } while(TRUE);
            }
            /* }}} */

            /* declare optimization {{{ */
            if ($op['op'] == 'declare' || $op['op'] == 'append_var') {
                /* Code optimization
                **
                **  If a variable declaration, or append variable is followed
                **  by several append_var, then merge everything into a 
                **  single STMT.
                **
                */
                do {
                    $next_op = $op_code[$i+1];
                    if (!isset($next_op) || $next_op['op'] != 'append_var' || $next_op['name'] != $op['name']) {
                        break;
                    }
                    for ($e=0; $e < count($next_op); $e++) {
                        if (!isset($next_op[$e])) {
                            break;
                        }
                        $op[] = $next_op[$e];
                    }
                    $i++;
                } while(TRUE);
            }
            /* }}} */

            $method = "php_{$op['op']}";
            if (!is_callable(array($this, $method))) {
                throw new Exception("CodeGenerator: Missing method $method");
            }
            switch ($op['op']) {
            case 'end_for':
            case 'end_foreach':
            case 'end_if':
            case 'end_function':
            case 'else':
                break;
            default:
                $code .= $this->ident();
            }
            $code .= $this->$method($op);
        }
        return $code;
    }
    // }}}

    // ident() {{{
    /**
     *  Get the string for the current tabulation
     *
     *  @return string
     */
    protected function ident()
    {
        $code = PHP_EOL;
        $code .= str_repeat($this->tab, $this->ident);

        return $code;
    }
    // }}}

    // php_else() {{{
    /**
     *  Return code for "else" 
     *
     *  @return string
     */
    protected function php_else()
    {
        $this->ident--;
        $code = $this->ident()."} else {";
        $this->ident++;
        return $code;
    }
    // }}}

    // php_comment() {{{
    /**
     *  Return code for "comments" 
     *
     *  @return string
     */
    function php_comment($op)
    {
        return "/* {$op['comment']} */";
    }
    // }}}

    // php_function(array $op) {{{
    /** 
     *  Return the function declaration of the class, for now
     *  it has fixed params, this should change soon to generate
     *  any sort of functions
     *
     *  @return string
     */
    function php_function($op)
    {
        $code = "function {$op['name']}(\${$this->scopeVariableName}, \$return=FALSE, \$blocks=array())".$this->ident()."{";
        $this->ident++;
        return $code;
    }
    // }}}

    // php_if(array $op) {{{
    /**
     *  Return the "if" declaration and increase $this->ident
     *
     *  @return string
     */
    protected function php_if($op)
    {
        $code  = "if (".$this->php_generate_expr($op['expr']).") {";
        $this->ident++;
        return $code;
    }
    // }}}

    // php_expr($op) {{{
    /**
     *  Return a stand-alone statement
     *
     *  @return string
     */
    protected function php_expr($op)
    {
        return $this->php_generate_expr($op[0]).";";
    }
    // }}}

    // php_end_block() {{{
    /**
     *  Finish the current block (if, for, function, etc),
     *  return the final "}", and decrease $this->ident
     *
     *  @return string
     */
    protected function php_end_block()
    {
        $this->ident--;
        return $this->ident()."}";    
    }
    // }}}

    // php_end_function() {{{
    /**
     *  Return code to end a function
     *
     *  @return string
     */
    protected function php_end_function()
    {
        return $this->php_end_block();
    }
    // }}}

    // php_end_if() {{{
    /**
     *  Return code to end a if
     *
     *  @return string
     */
    protected function php_end_if()
    {
        return $this->php_end_block();
    }
    // }}}

    // php_end_for() {{{
    /**
     *  Return code to end a for
     *
     *  @return string
     */
    protected function php_end_for()
    {
        return $this->php_end_block();
    }
    // }}}

    // php_end_foreach() {{{
    /**
     *  Return code to end a foreach
     *
     *  @return string
     */
    protected function php_end_foreach()
    {
        return $this->php_end_block();
    }
    // }}}

    // php_for() {{{
    /**
     *
     */
    protected function php_for($op)
    {
        $index = $this->php_get_varname($op['index']);
        foreach (array('min', 'max', 'step') as $type) {
            if (is_array($op[$type])) {
                $$type = $this->php_get_varname($op[$type]['var']);
            } else {
                $$type = $op[$type];
            }
        }
        $cmp  = "<=";
        if (is_numeric($step) && $step < 0) {
            $cmp = ">=";
        }
        if (is_numeric($min) && is_numeric($max) && $max < $min) {
            if (is_numeric($step) && $step > 0) {
                $step *= -1;
            }
            $cmp = ">=";
        }

        $code = "for ({$index} = {$min}; {$index} {$cmp} {$max}; {$index} += {$step}) {"; 
        $this->ident++;

        return $code;
    }
    // }}}

    // php_foreach(array $op)  {{{
    /**
     *  Return the declaration of a "foreach" statement.
     *
     *  @return string
     */
    protected function php_foreach($op)
    {
        $op['array'] = $this->php_get_varname($op['array']);
        $op['value'] = $this->php_get_varname($op['value']);
        $code = "if (is_array({$op['array']})) foreach ({$op['array']} as ";
        if (!isset($op['key'])) {
            $code .= " {$op['value']}";
        } else {
            $op['key'] = $this->php_get_varname($op['key']);
            $code     .= " {$op['key']} => {$op['value']}";
        }

        $code .= ") {";
        $this->ident++;
        return $code;
    }
    // }}}

    // php_append_var(array $op) {{{
    /**
     *  Return code to append something to a variable
     *
     *  @return string
     */
    protected function php_append_var($op)
    {
        return $this->php_declare($op, '.=');
    }
    // }}}

    // php_exec($op) {{{
    /**
     *  Return code for a function calling. 
     *
     *  @return string
     */
    protected function php_exec($op)
    {
        $code  = "";
        if (is_string($op['name'])) {
            $code .= $op['name'];
        } else {
            $function = $this->php_get_varname($op['name']);
            $code .= $function;
        }
        $code .= '(';
        if (isset($op['args'])) {
            $code .= $this->php_generate_list($op['args']);
        }
        $code .= ')';
        return $code;
    }
    // }}}

    // php_global($op) {{{
    function php_global($op)
    {
        return "global \$".implode(", \$", $op['vars']).";";
    }
    // }}}

    // php_generate_expr($op) {{{
    /**
     *  Return an expression
     *  
     *  @return string
     */
    protected function php_generate_expr($expr)
    {
        $code = '';
        if (is_object($expr)) {
            $expr = $expr->getArray();
        }
        if (is_array($expr) && isset($expr['op_expr'])) {
            if ($expr['op_expr'] == 'expr') {
                $code .= "(";
                $code .= $this->php_generate_expr($expr[0]);
                $code .= ")";
            } else if ($expr['op_expr'] == 'not') {
                $code .= "!".$this->php_generate_expr($expr[0]);
            } else {
                $code .= $this->php_generate_expr($expr[0]);
                if (is_object($expr['op_expr'])) {
                    var_dump($expr);die('unexpected error');
                }
                $code .= " {$expr['op_expr']} ";
                $code .= $this->php_generate_expr($expr[1]);
            }
        } else {
            if (is_array($expr)) {
                $code .= $this->php_generate_stmt(array($expr));
            } else {
                if ($expr === FALSE) {
                    $expr = 'FALSE';
                } else if ($expr === TRUE) {
                    $expr = 'TRUE';
                }
                $code .= $expr;
            }
        }
        return $code;
    }
    // }}}

    // php_generate_list(array ($array) {{{
    /**
     *  Return a list of expressions for parameters 
     *  of a function
     *
     *  @return string
     */
    protected function php_generate_list($array)
    {
        $code = "";
        foreach ($array as $value) {
            $code .= $this->php_generate_stmt(array($value));
            $code .= ", ";
        }
        return substr($code, 0, -2);
    }
    // }}}

    // php_generate_stmt(Array $op) {{{
    /**
     *  Return the representation of a statement
     *
     *  @return string
     */
    protected function php_generate_stmt($op, $concat='.')
    {
        $code = "";

        for ($i=0; $i < count($op); $i++) {
            if (!isset($op[$i])) {
                continue;
            }
            if (!is_Array($op[$i])) {
                throw new Haanga_Compiler_Exception("Malformed declaration ".print_r($op, TRUE));
            }
            $key   = key($op[$i]);
            $value = current($op[$i]); 
            switch ($key) {
            case 'array':
                $code .= "Array(";
                $code .= $this->php_generate_list($value);
                $code .= ")";
                break;
            case 'function':
            case 'exec':
                if (strlen($code) != 0 && $code[strlen($code) -1] != $concat) {
                    $code .= $concat;
                }

                $value = array('name' => $value, 'args' => $op[$i]['args']);
                $code .= $this->php_exec($value, FALSE);
                $code .= $concat;
                break;
            case 'key':
                $code .= $this->php_generate_stmt(array($value[0]))." => ".$this->php_generate_stmt(array($value[1]));
                break;
            case 'string':
                if ($code != "" && $code[strlen($code)-1] == "'") {
                    $code = substr($code, 0, -1);
                } else {
                    $code .= "'";
                }
                $html = addslashes_ex($value);
                $code .= $html."'";
                break;
            case 'var':
                if (strlen($code) != 0 && $code[strlen($code) -1] != $concat) {
                    $code .= $concat;
                }
                $code .= $this->php_get_varname($value). $concat;
                break;
            case 'number':
                if (!is_numeric($value)) {
                    throw new Exception("$value is not a valid number");
                }
                $code .= $value;
                break;
            case 'op_expr':
                if (strlen($code) != 0 && $code[strlen($code) -1] != $concat) {
                    $code .= $concat;
                }
                $code .= '(' . $this->php_generate_expr($op[$i]) . ')';
                $code .= $concat;
                break;
            case 'expr':
                if (strlen($code) != 0 && $code[strlen($code) -1] != $concat) {
                    $code .= $concat;
                }
                $code .= $this->php_generate_expr($value);
                $code .= $concat;
                break;
            case 'expr_cond':
                if (strlen($code) != 0 && $code[strlen($code) -1] != $concat) {
                    $code .= $concat;
                }
                $code .= "(";
                $code .= $this->php_generate_expr($value);
                $code .= " ? ";
                $code .= $this->php_generate_stmt(array($op[$i]['true']));
                $code .= " : ";
                $code .= $this->php_generate_stmt(array($op[$i]['false']));
                $code .= "){$concat}";
                break;
            case 'constant':
                $code = $value;
                break;
            default:
                throw new Exception("Don't know how to declare {$key} = {$value} (".print_r($op, TRUE));
            }
        }

        if ($code != "" && $code[strlen($code)-1] == $concat) {
            $code = substr($code, 0, -1);
        }

        return $code;
    }
    // }}}

    // php_print(array $op) {{{
    /**
     *  Return an echo of an stmt
     *
     *  @return string
     */
    protected function php_print($op)
    {
        $output = $this->php_generate_stmt($op, Haanga_Compiler::getOption('echo_concat'));
        if ($output == "' '" && Haanga_Compiler::getOption('strip_whitespace')) {
            return; /* ignore this */
        }
        return 'echo '.$output.';';
    }
    // }}}

    // php_inc(array $op) {{{
    /**
     *  Return increment a variable ($var++)
     *
     *  @return string
     */
    protected function php_inc($op)
    {
        return "++".$this->php_get_varname($op['name']);
    }
    // }}}

    // php_declare(array $op, $assign='=') {{{
    /**
     *  Return a variable declaration
     *
     *  @return string  
     */
    protected function php_declare($op, $assign=' =')
    {
        $op['name'] = $this->php_get_varname($op['name']);
        $code = "{$op['name']} {$assign} ".$this->php_generate_stmt($op).";";
        return $code;
    }
    // }}}

    // php_get_varname(mixed $var) {{{
    /**
     *  Return a variable
     *
     *  @param mixed $var
     *
     *  @return string
     */
    protected function php_get_varname($var)
    {
        if (is_array($var)) {
            if (!is_string($var[0])) {
                if (count($var) == 1) {
                    return $this->php_get_varname($var[0]);
                } else {
                    throw new Exception("Invalid variable definition ".print_r($var, TRUE));
                }
            }
            $var_str = $this->php_get_varname($var[0]);
            for ($i=1; $i < count($var); $i++) {
                if (is_string($var[$i])) {
                    $var_str .= "['".addslashes_ex($var[$i])."']";
                } else if (is_array($var[$i])) {
                    if (isset($var[$i]['var'])) {
                        /* index is a variable */
                        $var_str .= '['.$this->php_get_varname($var[$i]['var']).']';
                    } else if (isset($var[$i]['string'])) {
                        /* index is a string */
                        $var_str .= "['".addslashes_ex($var[$i]['string'])."']";
                    } else if (isset($var[$i]['number'])) {
                        /* index is a number */
                        $var_str .= '['.$var[$i]['number'].']';
                    } else if (isset($var[$i]['object'])) {
                        /* Accessing a object's property */
                        if (is_array($var[$i]['object'])) {
                            $var_str .= '->{'.$this->php_get_varname($var[$i]['object']['var']).'}';
                        } else {
                            $var_str .= '->'.$var[$i]['object'];
                        }
                    } else if (isset($var[$i]['class'])) {
                        /* Accessing a class' property */
                        $var_str = substr($var_str, 1);
                        if (is_array($var[$i]['class'])) {
                            $var_str .= '::{'.$this->php_get_varname($var[$i]['class']['var']).'}';
                        } else {
                            $var_str .= '::'.$var[$i]['class'];
                        }
                    } else if ($var[$i] === array()) {
                        /* index is a NULL (do append) */
                        $var_str .= '[]';
                    } else {
                        throw new Haanga_Compiler_Exception('Unknown variable definition '.print_r($var, TRUE));
                    }
                }
            }
            return $var_str;
        } else {
            return "\$".$var;
        }
    }
    // }}}

    // php_return($op) {{{
    /**
     *  Return "return"
     *
     *  @return string
     */
    protected function php_return($op)
    {
        $code = "return ".$this->php_generate_stmt($op).";";
        return $code;
    }
    // }}}

}

/*
 * Local variables:
 * tab-width: 4
 * c-basic-offset: 4
 * End:
 * vim600: sw=4 ts=4 fdm=marker
 * vim<600: sw=4 ts=4
 */
