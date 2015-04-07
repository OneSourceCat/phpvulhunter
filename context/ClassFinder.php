<?php


require CURR_PATH . '/utils/FileUtils.class.php';
require CURR_PATH . '/vendor/autoload.php' ;

ini_set('xdebug.max_nesting_level', 2000);

/**
 *  上下文对象
 *  存储全局信息（类信息）
 *  要做成单例模式
 * @author Exploit
 *
 */

class Context {
	private static $instance ;   //单例
	public $records ;    //上下文中全部类的记录
	
	/**
	 * 构造方法私有化
	 */
	private function __construct(){
		$this->records = array() ;
	}

	/*
		在Context中设置新的一条记录
	*/
	public function setRecord($record){
		array_push($this->records,$record) ;
	}

	/*
		更新上下文的记录
		$mode  更新的类型，包括更新类属性和类方法
		$classname  要更新的类名字
		$updateinfo  要更新的内容
	*/
	public function updateRecord($mode,$classname,$updateinfo=array()){
		$oldrecord = NULL ;
		foreach ($this->records as $key => $value) {
			if($value['name'] == $classname){
				$oldrecord = $value ;
				break ;
			}
		}
		if(!$oldrecord) die('上下文中没有找到该类') ;
		switch ($mode) {
			case 'properties':
				array_push($oldrecord['class_properties'],$updateinfo) ;
				break;
			
			case 'methods':
				array($oldrecord['class_methods'],$updateinfo) ;
				break;
		}
	}

	/*
		获取类中的一个函数的AST节点
		@param $funcname  给定一个方法的名称，没法知道调用的函数在那个php文件，当类名：：函数名都相同概率低，不影响得到函数歧义
		@return 返回相应的AST上的方法节点
	*/
	public function getFunctionBody($funcname){
		$method = NULL ;
		$path = '';
		$code = '' ;
		$records = $this->records ;
		$funcinfo = explode(":", $funcname);
		if(count($funcinfo)>1){
		    $classname = $funcinfo[0];
		    $funcname = $funcinfo[1];
		}else{
		    $funcname = $funcinfo[0];
		}
		//echo "$classname";
		//print_r($records);
		//寻找相应的method
		for($i=0;$i<count($records);$i++) {
			foreach($this->records[$i]->class_methods as $k => $item){
				if($item['name'] ==  $funcname ){
					$method = $item ;
					$path = $this->records[$i]->path;
				}
			}
		}

		//设置code
		if (!$path)
		    return null;
		$code = file_get_contents($path) ;
		
		//找到了相应的方法名称
		if($method && $code){
			$startLine = $method['startLine'] ;
			$endLine = $method['endLine'] ;

			$parser = new PhpParser\Parser(new PhpParser\Lexer\Emulative) ;
			$visitor = new FunctionBodyVisitor ;
			$traverser = new PhpParser\NodeTraverser ;
			$visitor->startLine = $startLine ;
			$visitor->endLine = $endLine ;

			
			$stmts = $parser->parse($code) ;
			$traverser->addVisitor($visitor) ;
			$traverser->traverse($stmts) ;
			return $visitor->getFunctionBody() ;

		}else{
			return NULL ;
		}
	}
	
	public static function getInstance(){
		if(!(self::$instance instanceof self)){
			self::$instance = new self ;
		}
		return self::$instance ;
	}
	
	private function __clone(){
	
	}

}

/*
	记录类
	是Context中的一条记录
	For_example:
	'Class_name'=>'Test',
	'Class_properties'=>array('ip','name'),
	'Class_methods'=>array(
						0=>array('name'=>'test','params'=>array('a','b'),'startLine'=>1,'endLine'=>5),
						1=>array('name'=>'hello','params'=>NULL,'startLine'=>1,'endLine'=>5)
                     ),
    'path'=>"c:/1.php"
                    
*/
class Record{
	public $class_name ;   //类名称
	public $class_properties;   //类的属性
	public $class_methods;  //类的方法
	public $class_implements;  //类实现的接口
	public $class_extends ;  //类继承的父类
	public $path ;   //类所在的存储路径
	public function __construct(){
		$this->class_name = '';
		$this->class_properties = array();
		$this->class_methods = array() ;
		$this->class_implements = array() ;
		$this->class_extends = '' ;
		$this->path = '' ;
	}

}


use PhpParser\Node ;
class ClassVisitor extends PhpParser\NodeVisitorAbstract{

	public $class_path = '' ;   //当前正在扫描的文件名

	public function leaveNode(Node $node){
		if($node instanceof Node\Stmt\Class_){
			$record = new Record ;
			$record->path = $this->class_path ;
			//设置类的名字
			echo "Class_name:$node->name<br/>";
			$record->class_name = $node->name ;

			if($node->extends) $record->class_extends = $node->extends->parts[0];
			$record->class_implements = $node->implements ;

			//设置类的成员变量
			echo "Class_properties:" ;
			$props = array() ;
			foreach($node->stmts as $key => $value){
				//找到类属性信息
				if($value instanceof Node\Stmt\Property){
					echo $value->props[0]->name ."<br/>";
					array_push($props,$value->props[0]->name) ;
				}
			}
			$record->class_properties = $props ;

			//设置类的方法
			echo "Class_methods:<br/>" ;
			foreach ($node->stmts as $key => $value) {
				if($value instanceof Node\Stmt\ClassMethod){
					//初始化类方法的描述
					$method = array('name'=>'','params'=>array(),'startLine'=>0,'endLine'=>0);
					
					//设置方法名称
					echo "[methods_name]:" .$value->name ."<br/>";
					$method['name'] = $value->name ;

					//设置方法的参数
					echo "[methods_params]:";
					for($i=0;$i<count($value->params);$i++){
						echo $value->params[$i]->name ."\t";
						array_push($method['params'],$value->params[$i]->name) ;
					}
					echo "<br>" ;

					//设置方法的起始行号和终止行号
					echo "[method_Lineinfo]:" ;
					$method['startLine'] = $value->getAttribute("startLine") ;
					$method['endLine'] = $value->getAttribute("endLine") ;
					//echo "startLine:$method['startLine'],endLine:$method['endLine']" ;
					echo "<br>" ;

					array_push($record->class_methods,$method);
				}
				
			}

			$context = Context::getInstance() ;
			$context->setRecord($record) ;
		}
		elseif ($node instanceof Node\Stmt\Function_){
		    $record = new Record ;
		    $record->path = $this->class_path ;
		    //设置类的名字
		    $record->class_name = '' ;
		    $record->class_properties = '' ;
		    //设置类的方法
		    //初始化类方法的描述
		    $method = array('name'=>'','params'=>array(),'startLine'=>0,'endLine'=>0);
		
		    //设置方法名称
		    echo "[methods_name]:" .$node->name ."<br/>";
		    $method['name'] = $node->name ;
		
		    //设置方法的参数
		    echo "[methods_params]:";
		    for($i=0;$i<count($node->params);$i++){
		        echo $node->params[$i]->name ."\t";
		        array_push($method['params'],$node->params[$i]->name) ;
		    }
		    echo "<br>" ;
		
		    //设置方法的起始行号和终止行号
		    echo "[method_Lineinfo]:" ;
		    $method['startLine'] = $node->getAttribute("startLine") ;
		    $method['endLine'] = $node->getAttribute("endLine") ;
		    //echo "startLine:$method['startLine'],endLine:$method['endLine']" ;
		    echo "<br>" ;
		     
		    array_push($record->class_methods,$method);
		
		
		    $context = Context::getInstance() ;
		    $context->setRecord($record) ;
		}
	}
}


/*
	用来获取方法体的遍历
*/
class FunctionBodyVisitor extends PhpParser\NodeVisitorAbstract{
	public $func_body = NULL ;
	public $startLine ;
	public $endLine ;

	public function leaveNode(PhpParser\Node $node){
		if(($node->getAttribute('startLine') == $this->startLine) && ($node->getAttribute('endLine') == $this->endLine)){
			$this->func_body = $node ;
		}
	}

	public function getFunctionBody(){
		return $this->func_body ;
	}

}


/*
	遍历出审计工程中的所有代码
	并抽取出所有类的信息
*/
class ClassFinder{
	private $parser = NULL ;   //代码解析器
	//private $fileUtil = NULL ;  //文件工具类
	private $visitor = NULL ;   //访问者
	private $traverser  = NULL;  //遍历AST对象
	private $path = '' ;   //工程入口路径
	
	/*
		构造函数
	*/
	public function __construct($path){
		$this->path = $path ;
		$this->parser = new PhpParser\Parser(new PhpParser\Lexer\Emulative) ;
		$this->visitor = new ClassVisitor ;
		$this->traverser = new PhpParser\NodeTraverser ;
		$this->traverser->addVisitor($this->visitor) ;
	}

	/*
		获取所有的源文件的路径
	*/
	private function getAllSourceFiles(){
		//return $this->fileUtil->getPHPfile($this->path) ;
		return FileUtils::getPHPfile($this->path);
	}

	/*
		获取上下文
		使用AST进行类定义判断，通过识别相应的节点类型来对类的信息进行收集
		收集完成之后，将信息设置到Context中（序列化）
	*/
	public function getContext(){
		//判断本地序列化文件中是否存在Context
		if(($serial_str = file_get_contents(CURR_PATH . "/data/serialdata"))!=''){
			$records = unserialize($serial_str) ;
			$context = Context::getInstance() ;
			$context->records = $records ;
			return ;
		}

		$filearr = $this->getAllSourceFiles() ;
		$len = count($filearr) ;
		for($i=0;$i<$len;$i++){
			$this->visitor->class_path = $filearr[$i] ;
			$code = file_get_contents($this->visitor->class_path);
			try{
				$stmts = $this->parser->parse($code) ;	
			}catch (PhpParser\Error $e) {
    			echo 'Parse Error: ', $e->getMessage();
    			continue ;
			}
			
			$this->traverser->traverse($stmts) ;  //遍历AST
		}

		
		//补充类通过继承获得的属性和方法
		$context = Context::getInstance() ;
		$this->getExtendsInfo($context) ;

		//对Context进行序列化，加快下次读取速度
		$this->serializeContext($context) ;
	}

	public function serializeContext($context){
		file_put_contents(CURR_PATH . "/data/serialdata",serialize($context->records)) ;
	}

	/*
		通过继承关系对类继承的属性和方法进行补充
		@param $context上下文对象
	*/
	public function getExtendsInfo($context){
		foreach ($context->records as $key => $record) {
			if($record->class_extends){
				//发现继承父类的类记录
				$parent = NULL ;
				foreach ($context->records as $key => $value) {
					$extends_info = $record->class_extends ;
					if($value->class_name == $extends_info){
						$parent = $value ;
					}else{
						continue ;
					}
				}
				if(!$parent) continue ;
				$record->class_properties = array_merge($record->class_properties, $parent->class_properties) ;
				$record->class_methods = array_merge($record->class_methods,$parent->class_methods) ;
			}
		}
	}


}


//具体使用方法
//$path = "E:/School_of_software/information_security/PHPVulScanner_project/CMS/chengshiCMS/Cscmsv3.5.6/upload" ;
//$path = "source.class.php" ;
//$path = "./test" ;
$path = CURR_PATH . '/test/simple_demo.php';
$finder = new  ClassFinder($path) ;
$finder->getContext() ;
$context = Context::getInstance() ;
echo "<pre>" ;
// print_r($context->records) ;

//$node = $context->getFunctionBody("ClassBase:c");
// print_r($node);

?>