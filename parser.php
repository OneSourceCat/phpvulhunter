<?php
require './vendor/autoload.php' ;
ini_set('xdebug.max_nesting_level', 2000);
echo "<pre>" ;
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
	array('mysql_query',array(1)),
) ;


use PhpParser\Node ;
class MyVisitor extends PhpParser\NodeVisitorAbstract{
	public $concat = array();
// 	public function leaveNode(Node $node){
// 		$type = $node->getType() ;
// 		if($type == "Expr_BinaryOp_Concat"){
// 			$this->concat = $node ;
// 			return ;
// 		}
// 		//print_r($node) ;
// 	}

	public function leaveNode(Node $node){
		$type = $node->getType() ;
		if($type == "Expr_AssignOp_Concat"){
			array_push($this->concat, $node) ;
		}
		//$this->concat = $node ;
	}
	
}

$parser = new PhpParser\Parser(new PhpParser\Lexer\Emulative) ;
$traverser = new PhpParser\NodeTraverser ;

//$code = file_get_contents('source.class2.php') ;
//$code = file_get_contents('./test/sub.php') ;
$code = file_get_contents('./test/simple_demo.php') ;

$stmts = $parser->parse($code) ;
echo "<pre>" ;
//print_r($stmts) ;
$visitor = new MyVisitor() ;
$traverser->addVisitor($visitor) ;
$traverser->traverse($stmts) ;

//print_r($visitor->concat) ;

require './symbols/ConcatSymbol.class.php';
$symbol = new ConcatSymbol() ;

foreach ($visitor->concat as $con){
	//print_r($con) ;
	$symbol->setItemByNode($con) ;
}

echo count($symbol->getItems()) ;
print_r($symbol->getItems()) ;

































?>