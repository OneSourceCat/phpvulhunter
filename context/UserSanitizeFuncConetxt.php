<?php

require_once '/ClassFinder.php';
require_once CURR_PATH . '/vendor/autoload.php' ;
require_once CURR_PATH . '/utils/NodeUtils.class.php';

use PhpParser\Node;

/**
 * @author xyw55
 *   用户自定义净化函数净化信息对象
 *   存储全局用户定义净化函数
 *   $sanitizeFunctions map形式，存储净化函数对象
 *   要做成单例模式
 */
class UserSanitizeFuncConetxt{
    public $sanitizeFunctions; //存储全局用户定义净化函数
    private static $instance ;   //单例
    
    private function __construct(){
        $this->sanitizeFunctions = array();
    }
    
    //添加一个净化函数
    public function addFunction($onefunction){
        array_push($this->sanitizeFunctions, $onefunction);
    }
    //得到某函数的净化信息，未净化，返回null
    public function getFuncSanitizeInfo($className,$funcName){
        foreach ($this->sanitizeFunctions as $oneFunction){
            if (($className == $oneFunction->className) && ($funcName == $oneFunction->functionName))
                return $oneFunction;
            else 
                return null;
        }
    }
    //获得实例
    public static function getInstance(){
        if(!(self::$instance instanceof self)){
            self::$instance = new self ;
        }
        return self::$instance ;
    }
  
    private function __clone(){
    }
}

/**
 * 存储一个函数的净化信息
 * @author xyw55
 *
 */
class OneFunction{
    public $className ;
    public $functionName ;
    public $sanitizeParams ;
    
    public function __construct($className,$functionName){
        $this->className = $className;
        $this->functionName = $functionName;
        $this->sanitizeParams = array();
    }
    
    /**
     * 添加一个净化参数
     * @param 参数位置 $pos
     * @param 参数净化类型 $type
     */
    public function addSanitizeParam($pos,$type){
        array_push($this->sanitizeParams, array('positon' => $pos,'type' =>$type));
    } 
    //getter
    public function getSanitizeParams(){
        return $this->sanitizeParams;
    }   
}


//处理函数体，
//遇到函数调用，先查找安全函数，其次在context中查找用户自定义函数，递归查找那个自定义函数中的参数净化
class SanitizeParamsFinder{
    private $oneFunction;
    private $className;
    private $methodName;
    
    public function __construct($className,$methodName){
        $this->className = $className;
        $this->methodName = $methodName;
        $this->oneFunction = new OneFunction($className,$methodName);
    }
    
    /**
     * @param 函数体 $stmts
     * @param 函数参数 $params
     * @return 一个函数的净化信息对象
     */
    function findSanitizeParam($stmts,$params){
        //print_r($stmts);
        //print_r($params);
        foreach ($stmts as $node){
            $type = $node->getType();
            switch ($type){
                //function 
                case "Expr_FuncCall":
                    $funcname = NodeUtils::getNodeFunctionName($node);
                    echo $funcname."<br/>";
                    //查找函数是否在净化函数中，净化类别
                    $ret = $this->isSecureFunction($funcname);
                    if($ret[0]){
                        //默认净化函数净化所有参数
                        foreach ($node->args as $arg){
                            $argName = NodeUtils::getNodeStringName($arg);
                            $pos = $this->searchPos($argName, $params);
                            if ($pos>-1){
                                //当函数的第1个参数净化时，数组为0，记为1
                                $this->oneFunction->addSanitizeParam(($pos+1), $ret['type']);
                            }
                        }                                               
                    }else{
                        //user define function
                        //find function body in context
                        $context = Context::getInstance();           
                        $funcnode = $context->getFunctionBody($funcname);
                        if (!$funcnode)
                            break;
                        //递归，return onefunction
                        $next = new SanitizeParamsFinder(null, $funcname);
                        $ret = $next->findSanitizeParam($funcnode->stmts, $funcnode->params); 
                        if(!$ret)
                            break;                     
                        //根据return onefunction，加入到this->onefunction
                        foreach ($ret->getSanitizeParams() as $param){
                            //计算参数位置，因为认为第一个参数设为位置1，而AST树中 是从0开始
                            $postion = $param['positon']-1;
                            $pos = $this->searchPos(NodeUtils::getNodeStringName($node->args[$postion]), $params);
                            if ($pos>-1){
                                //当函数的第1个参数净化时，数组为0，记为1
                                $this->oneFunction->addSanitizeParam(($pos+1), $param['type']);
                            }
                        }
                    }
                    break;
                //class method
                case "Expr_MethodCall":
                //class static method
                case "Expr_StaticCall":
                    $funcname = NodeUtils::getNodeFunctionName($node);
                    echo $funcname."<br/>";
                    //查找函数是否在净化函数中，净化类别
                    $ret = $this->isSecureFunction($funcname);
                    if($ret[0]){
                        //默认净化函数净化所有参数
                        foreach ($node->args as $arg){
                            $argName = NodeUtils::getNodeStringName($arg);
                            $pos = $this->searchPos($argName, $params);
                            if ($pos>-1){
                                //当函数的第1个参数净化时，数组为0，记为1
                                $this->oneFunction->addSanitizeParam(($pos+1), $ret['type']);
                            }
                        }        
                    }else{
                        //user define function
                        //find function body in context
                        $context = Context::getInstance();
                        $funcnode = $context->getFunctionBody($funcname);
                        if (!$funcnode)
                            break;
                        //递归，return onefunction
                        $next = new SanitizeParamsFinder($this->className, $funcname);
                        $ret = $next->findSanitizeParam($funcnode->stmts, $funcnode->params);
                        if(!$ret)
                            break;
                        //根据return onefunction，加入到this->onefunction
                        foreach ($ret->getSanitizeParams() as $param){
                            //计算参数位置，因为认为第一个参数设为位置1，而AST树中 是从0开始
                            $postion = $param['positon']-1;
                            $pos = $this->searchPos(NodeUtils::getNodeStringName($node->args[$postion]), $params);
                            if ($pos>-1){
                                //当函数的第1个参数净化时，数组为0，记为1
                                $this->oneFunction->addSanitizeParam(($pos+1), $param['type']);
                            }
                        }
                    }
                    break;
                case "Stmt_Return":
                    //处理return中的函数调用
                    if ($node->expr->getType() != "Expr_FuncCall"){
                        break;
                    }
                    $funcName = NodeUtils::getNodeStringName($node->expr->name);
                    //递归，return onefunction
                    $next = new SanitizeParamsFinder(null, $funcName);
                    
                    $ret = $next->findSanitizeParam(array($node->expr), $node->expr->args); 
                    if(!$ret)
                        break;
                    //根据return onefunction，加入到this->onefunction
                    foreach ($ret->getSanitizeParams() as $param){
                        //计算参数位置，因为认为第一个参数设为位置1，而AST树中 是从0开始
                        $postion = $param['positon']-1;
                        $pos = $this->searchPos(NodeUtils::getNodeStringName($node->expr->args[$postion]), $params);
                        if ($pos>-1){
                            //当函数的第1个参数净化时，数组为0，记为1
                                $this->oneFunction->addSanitizeParam(($pos+1), $param['type']);
                        }
                    }
                    break;
                case "Expr_Assign":
                    //处理赋值右边中的函数调用
                    if ($node->expr->getType() != "Expr_FuncCall"){
                        break;
                    }
                    $funcName = NodeUtils::getNodeStringName($node->expr->name);
                    //递归，return onefunction
                    $next = new SanitizeParamsFinder(null, $funcName);
                    
                    $ret = $next->findSanitizeParam(array($node->expr), $node->expr->args); 
                    if(!$ret)
                        break;
                    //根据return onefunction，加入到this->onefunction
                    foreach ($ret->getSanitizeParams() as $param){
                        //计算参数位置，因为认为第一个参数设为位置1，而AST树中 是从0开始
                        $postion = $param['positon']-1;
                        $pos = $this->searchPos(NodeUtils::getNodeStringName($node->expr->args[$postion]), $params);
                        if ($pos>-1){
                            $this->oneFunction->addSanitizeParam(($pos+1), $param['type']);
                        }
                    }
                    break;
                default:
                    break;
            }
        }
        if($this->isSanitizeFunc($this->oneFunction))
            return $this->oneFunction;
    }
    //检查函数是否有净化参数
    public function isSanitizeFunc($oneFunction){
        return count($oneFunction->getSanitizeParams());       
    }
    // 检测是否为净化函数
    public function isSecureFunction($funcName){ 
        global $F_SECURES_ARRAY,$F_SECURES_ALL;
        $nameNum = count($F_SECURES_ARRAY); 
        if (in_array($funcName, $F_SECURES_ALL)){
            $type = array();
            for($i = 0;$i < $nameNum; $i++){
		    	if(in_array($funcName, $F_SECURES_ARRAY[$i])){
		    	    print_r("      find<br/>");
		    	    array_push($type, $F_SECURES_ARRAY[$i]['__NAME__']);
		    		//return array(true,'type'=>$F_SECURES_ARRAY[$i]['__NAME__']);
		    	}
	    	}
	    	if($type)
	    	    return array(true,'type'=>$type);
	    	print_r("      not find<br/>");
    		return array(false);
        }else{
            print_r("      not find<br/>");
            return array(false);
        }
    }
    /**
     * 查找参数在参数列表的位置
     * @param 参数 $paramName
     * @param 参数列表 $params
     * @return 参数位置
     */
    public function searchPos($paramName,$params){
        $count = 0;
        foreach ($params as $param){
            if (NodeUtils::getNodeStringName($param) == $paramName)
                return $count;
            $count++;
        }
        return -1;
    }
    
}

class UserSanitiFuncFinder{
    private $parser = NULL ;   //代码解析器
    private $visitor = NULL ;   //访问者
    private $traverser  = NULL;  //遍历AST对象
    private $path = '' ;   //工程入口路径
    /*
                构造函数
     */
    public function __construct($path){
        $this->path = $path ;
        $this->parser = new PhpParser\Parser(new PhpParser\Lexer\Emulative) ;
        $this->visitor = new SanitizeFuncVisitor ;
        $this->traverser = new PhpParser\NodeTraverser ;
        $this->traverser->addVisitor($this->visitor) ;
    }
    
    /*
                获取所有的源文件的路径
     */
    private function getAllSourceFiles(){
        return FileUtils::getPHPfile($this->path);
    }
    
    /*
             获取UserSanitizeFuncConetxt
             使用AST对函数净化参数判断，对净化信息收集
             收集完成之后，将信息设置到UserSanitizeFuncConetxt中（序列化）
     */
    public function getUserSanitizeFuncConetxt(){
        //判断本地序列化文件中是否存在UserSanitizeFuncConetxt
        if(($serial_str = file_get_contents(CURR_PATH . "/data/sanitizeFuncConetxtSerialData"))!=''){
            $sanitizeFunctions = unserialize($serial_str) ;
            $funcContext = UserSanitizeFuncConetxt::getInstance() ;
            $funcContext->sanitizeFunctions = $sanitizeFunctions ;
            return ;
        }
    
        $filearr = $this->getAllSourceFiles() ;
        $len = count($filearr) ;
        for($i=0;$i<$len;$i++){
            $this->visitor->filePath = $filearr[$i] ;
            $code = file_get_contents($this->visitor->filePath);
            try{
                $stmts = $this->parser->parse($code) ;
            }catch (PhpParser\Error $e) {
                echo 'Parse Error: ', $e->getMessage();
                continue ;
            }
            	
            $this->traverser->traverse($stmts) ;  //遍历AST
        }
      
        $funcContext = UserSanitizeFuncConetxt::getInstance() ;  
        //对UserSanitizeFuncConetxt进行序列化，加快下次读取速度
        $this->serializeContext($funcContext) ;
    }
    
    public function serializeContext($funcContext){
        file_put_contents(CURR_PATH . "/data/sanitizeFuncConetxtSerialData",serialize($funcContext->sanitizeFunctions )) ;
    }
    

  
}

/**
 * AST tree 遍历，寻找净化函数
 * @author xyw55
 */
class SanitizeFuncVisitor extends PhpParser\NodeVisitorAbstract{
    private $nodes = array();
    private $className = '';
    public $filePath = '';
    public function beforeTraverse(array $nodes){
        $this->nodes = $nodes ;
    }
    //TODO：在这要得到文件的require_array,在获取函数体使用
    //净化函数获取funcbody时采用暂时使用getFuncBody
    public function enterNode(Node $node){
        //遇到函数，判断是否是净化函数，净化了的参数位置和净化类型
        $type = $node->getType();
        if ($type == "Stmt_Function"){
            $findSanitizeParams = new SanitizeParamsFinder(null, $node->name);
            $onefunction = $findSanitizeParams->findSanitizeParam($node->stmts, $node->params);
            if($onefunction){
                $funcContext = UserSanitizeFuncConetxt::getInstance();
                $funcContext->addFunction($onefunction);
            }   
        }elseif ($type == "Stmt_ClassMethod"){
            $findSanitizeParams = new SanitizeParamsFinder($this->className, $node->name);
            $onefunction = $findSanitizeParams->findSanitizeParam($node->stmts, $node->params);
            if($onefunction){
                $funcContext = UserSanitizeFuncConetxt::getInstance();
                $funcContext->addFunction($onefunction);
            }   
        }elseif ($type == "Stmt_Class"){
            $this->className = $node->name;
        }
    }

    //getter
    public function getNodes(){
        return $this->nodes ;
    }

}

$path = CURR_PATH . '/test/simple_demo.php';
$finder = new UserSanitiFuncFinder($path) ;
$finder->getUserSanitizeFuncConetxt() ;
$funcContext = UserSanitizeFuncConetxt::getInstance() ;

echo '<pre>';
//print_r($funcContext->sanitizeFunctions);


?>