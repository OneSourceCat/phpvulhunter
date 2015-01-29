<?php
require './vendor/autoload.php' ;
ini_set('xdebug.max_nesting_level', 2000);
// //获得一个解析类
// $parser = new PhpParser\Parser(new PhpParser\Lexer\Emulative);
// //要解析的代码
// /*$code = "<?php echo '1+2' , 'chongrui' " ;*/
// $code = '<?php $a = 1+2 ;';
// //生成$stmts ===>  AST
// $stmts = $parser->parse($code) ;

// echo "<pre>" ;
// print_r($stmts) ;

// echo $stmts[0]->getType();
// var_dump($stmts[0]->var->name);
// //var_dump($stmts[0]->exprs);
// //var_dump($stmts[0]->exprs[1]->value);
// // foreach ($stmts[0]->exprs[1] as $key => $value) {
// // 	echo "$key==>$value" ."<br/>";
// // }

// use PhpParser\Node;
// class MyNodeVisitor extends PhpParser\NodeVisitorAbstract{
// 	public function leaveNode(Node $node){
// 		if($node instanceof Node\Scalar\String){
// 			echo $node->value ."<br/>";
// 		}
// 	}
// }



// //get a parser
// $parser = new PhpParser\Parser(new PhpParser\Lexer\Emulative) ;
// //get a traverser
// $traverser = new PhpParser\NodeTraverser ;

// //add you own visitor
// $traverser->addVisitor(new MyNodeVisitor) ;
// $traverser->addVisitor(new PhpParser\NodeVisitor\NameResolver); 

/* $code = "<?php if(1<2){\$a='1';}else{\$a='2';}?>" ;*/

// $stmts = $parser->parse($code) ;
// $stmts = $traverser->traverse($stmts) ;

$pvf = array(
	array('mysql_query',array(1)) ;
) ;


$parser = new PhpParser\Parser(new PhpParser\Lexer\Emulative) ;
$traverser = new PhpParser\NodeTraverser ;

//$code = file_get_contents('source.class2.php') ;
//$code = file_get_contents('./test/sub.php') ;
$code = file_get_contents('./test/simple_demo.php') ;

$stmts = $parser->parse($code) ;
echo "<pre>" ;
print_r($stmts) ;


use PhpParser\Node ;
class MyVisitor extends PhpParser\NodeVisitorAbstract{
	public function beforeTraverse(array $nodes){}
	
	public function enterNode(PhpParser\Node $node){}
	
	public function leaveNode(PhpParser\Node $node){
		
	}
	
	public function afterTraverse(array $nodes){}
	
}


?>