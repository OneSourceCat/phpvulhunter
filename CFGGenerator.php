<?php

require_once 'global.php';

//定义PHP语句类别
$RETURN_STATEMENT = array('Stmt_Return') ;
$STOP_STATEMENT = array('Stmt_Throw','Stmt_Break','Stmt_Continue') ;
$LOOP_STATEMENT = array('Stmt_For','Stmt_While','Stmt_Foreach','Stmt_Do') ;
$JUMP_STATEMENT = array('Stmt_If','Stmt_Switch','Stmt_TryCatch','Expr_Ternary','Expr_BinaryOp_LogicalOr') ;

use PhpParser\Node ;
class CFGGenerator{
	//AST解析类
	private $parser ;  
	
	//AST遍历类
	private $traverser ;  
	
	//全局的filesummary对象
	private $fileSummary ;

	//构造器
	public function __construct(){
		$this->parser = new PhpParser\Parser(new PhpParser\Lexer\Emulative) ;
		$this->traverser = new PhpParser\NodeTraverser ;
		$this->fileSummary = new FileSummary() ;
	}	
	
	//fileSummary的get方法
	public function getFileSummary() {
	    return $this->fileSummary;
	}
	
	//fileSummary的set方法
	public function setFileSummary($fileSummary) {
	    $this->fileSummary = $fileSummary;
	}
	
	/**
	 * 给定一个JUMP类型的Statement，获取分支node
	 * @param $node为AST节点（如 If,While等）
	 */
	public function getBranches($node){
		$type = $node->getType();   //获取AST节点的语句类型
		$branches = array() ;   //分支数组
		
		switch ($type){
			case 'Stmt_If':
				//处理if-else结构中的if语句，包括条件和语句
				$if_branch = new Branch($node->cond, $node->stmts) ;
				array_push($branches,$if_branch) ;
				
				//处理elseifs,elseifs为索引数组,由cond和stmts构成
				$elseifs = $node->elseifs ;
				if($elseifs){
					foreach($elseifs as $if){
						$else_if = new Branch($if->cond, $if->stmts) ;
						array_push($branches,$else_if) ;
					}	
				}
				
				//处理else分支，由stmts组成，没有cond，这里的cond填为"else"
				if($node->else){
					$else_branch = new Branch('else', $node->else->stmts) ;
					array_push($branches,$else_branch) ;
				}
				break ;
				
			case 'Stmt_Switch':
				//switch语句中的判断条件
				$cases = $node->cases ;
				foreach($cases as $case){
					//switch+case的condition
					$cond_arr = array($node->cond) ;
					array_push($cond_arr,$case->cond) ;
					
					//创建分支
					$case_branch = new Branch($cond_arr, $case->stmts) ;
					array_push($branches,$case_branch) ;
				}
				
				break ;
			
			case 'Stmt_TryCatch':
				//try分支
				$try_branch = new Branch(NULL, $node->stmts) ;
				
				//catch分支
				$catches = $node->catches ;
				foreach ($catches as $catch){
					$catch_branch = new Branch($catch->type, $catch->stmts) ;
					array_push($branches, $catch_branch) ;
				}
				
				break ;
			
			case 'Expr_Ternary':
				//三元运算 A?B:C
				$if_branch = new Branch($node->cond, $node->if) ;
				array_push($branches, $if_branch) ;
				$else_branch = new Branch('else', $node->else) ;
				array_push($branches,$else_branch) ;
				break ;
			
			case 'Expr_BinaryOp_LogicalOr':
				//A or B的逻辑或运算
				$visitor = new BranchVisitor() ;
				$this->traverser->addVisitor($visitor) ;
				$this->traverser->traverse(array($node)) ;
				$branches = $visitor->branches ;
				break ;
				
		}
		
		return $branches ;
	}
	
	
	/**
	 * 处理循环结构，将循环变量添加到基本块
	 * @param $node  AST Node
	 * @param $block  BasicBlock
	 */
	public function addLoopVariable($node,$block){
		//print_r($block) ;
		switch ($node->getType()){
			case 'Stmt_For':  //for(i=0;i<3;i++) ===> extract var i
				$block->loop_var = $node->init[0] ;
				break ;
			case 'Stmt_While':  //while(cond) ====> extract cond
				$block->loop_var = $node->cond ;
				break ;
			case 'Stmt_Foreach':  //foreach($nodes as $node) ======> extract $nodes
				$tempNode = clone $node ;
				unset($tempNode->stmts) ;
				$block->loop_var =  $tempNode ;
				break ;
			case 'Stmt_Do':   //do{}while(cond); =====> extract cond
				$block->loop_var = $node->cond ;
				break ;
		}
		//将循环条件加入到$block中
		$block->addNode($block->loop_var) ;
		unset($block->loop_var) ;
	}
	
	/**
	 * 给定AST Nodes集合，返回结束的行号
	 * @param unknown $nodes
	 */
	public function getEndLine($nodes){
		return end($nodes)->getAttribute('endLine') ;
	}
	
	/**
	 * 分析传入node赋值语句，以及当前block,
	 * 生成block summary中的一条记录
	 * @param ASTNode $node 赋值语句
	 * @param BasicBlock $block
	 * @param string $type 处理赋值语句的var和expr类型（left or right）
	 */
	private function assignHandler($node,$block,$dataFlow,$type){
		$part = null ;
		if($type == "left"){
			$part = $node->var ;
		}else if($type == "right"){
			$part = $node->expr ;
		}else{
			return ;
		}

		//处理$GLOBALS的赋值
		//$GLOBAL['name'] = "chongrui" ; 数据流信息为 $name = "chongrui" ;
		if ($part && SymbolUtils::isArrayDimFetch($part) && 
		      (substr(NodeUtils::getNodeStringName($part),0,7)=="GLOBALS")){
		    //加入dataFlow
		    $arr = new ArrayDimFetchSymbol() ;
		    $arr->setValue($part) ;
		    if($type == "left"){
		        $dataFlow->setLocation($arr) ;
		        $dataFlow->setName(NodeUtils::getNodeGLOBALSNodeName($part)) ;
		        //加入registerglobal
		        $this->registerGLOBALSHandler($part, $block);
		    }else if($type == "right"){
		        $dataFlow->setValue($arr) ;
		    }
		    return ;
		}
		
		
		//处理赋值语句，存放在DataFlow
		//处理赋值语句的左边
		if($part && SymbolUtils::isValue($part)){
			//在DataFlow加入Location以及name
			$vs = new ValueSymbol() ;
			$vs->setValueByNode($part) ;
			if($type == "left"){
				$dataFlow->setLocation($vs) ;
				$dataFlow->setName($part->name) ;
			}else if($type == "right"){
				$dataFlow->setValue($vs) ;
			}

		}elseif ($part && SymbolUtils::isVariable($part)){
			
			//加入dataFlow
			$vars = new VariableSymbol() ;
			$vars->setValue($part);
			if($type == "left"){
				$dataFlow->setLocation($vars) ;
				$dataFlow->setName($part->name) ;
			}else if($type == "right"){
				$dataFlow->setValue($part) ;
			}
			
		}elseif ($part && SymbolUtils::isConstant($part)){
			
			//加入dataFlow
			$con = new ConstantSymbol() ;
			$con->setValueByNode($part) ;
			$con->setName($part->name->parts[0]) ;
			if($type == "left"){
				$dataFlow->setLocation($con) ;
				$dataFlow->setName($part->name) ;
			}else if($type == "right"){
				$dataFlow->setValue($con) ;
			}
		}elseif ($part && SymbolUtils::isArrayDimFetch($part)){
			//加入dataFlow
			$arr = new ArrayDimFetchSymbol() ;
			$arr->setValue($part) ;
			$arr->setNameByNode($node);
			if($type == "left"){
				$dataFlow->setLocation($arr) ;
				$dataFlow->setName(NodeUtils::getNodeStringName($part)) ;
			}else if($type == "right"){
				$dataFlow->setValue($arr) ;
			}
		}elseif ($part && SymbolUtils::isConcat($part)){
			$concat = new ConcatSymbol() ;
			$concat->setItemByNode($part) ;
			if($type == "left"){
				$dataFlow->setLocation($concat) ;
				$dataFlow->setName($part->name) ;
			}else if($type == "right"){
				$dataFlow->setValue($concat) ;
			}
		}else{
			//不属于已有的任何一个symbol类型,如函数调用,类型转换
            if($part && ($part->getType() == "Expr_FuncCall" || 
                $part->getType() == "Expr_MethodCall" || 
			    $part->getType() == "Expr_StaticCall" ) ){
                
				//处理 id = urlencode($_GET['id']) ;
				if(!SymbolUtils::isValue($part)){
					$funcName = NodeUtils::getNodeFunctionName($part) ;
					BIFuncUtils::assignFuncHandler($part, $type, $dataFlow, $funcName) ;
				}
				
				//处理编码和净化信息
				if($type == 'right'){
				    //处理iconv等函数
				    //处理 id = urlencode($_GET['id']) ;
				    $singleFuncs = BIFuncUtils::getSingleFuncs();
				    $funcName = NodeUtils::getNodeFunctionName($part);
				    
				    if (array_key_exists($funcName, $singleFuncs)){
				        global $F_ENCODING_STRING ;
				        if (in_array($funcName, $F_ENCODING_STRING)){
				            EncodingHandler::setEncodeInfo($part, $dataFlow, $block, $this->fileSummary) ;
				        }
				        //将函数加入净化栈
				        $oneFunction = new OneFunction($funcName);
				        $dataFlow->getLocation()->addSanitization($oneFunction) ;
				    }else{
    					//检查是否为sink函数
     					$this->functionHandler($part, $block, $this->fileSummary);
     					
    					//处理净化信息和编码信息
    					SanitizationHandler::setSanitiInfo($part,$dataFlow, $block, $this->fileSummary) ;
    					EncodingHandler::setEncodeInfo($part, $dataFlow, $block, $this->fileSummary) ;
				    }
				}

			}
			
			//处理类型强制转换
			if($part 
			    && ($part->getType() == "Expr_Cast_Int" || $part->getType() == "Expr_Cast_Double") 
			    && $type == "right"){
			    $dataFlow->getLocation()->setType("int") ;
			    $symbol = SymbolUtils::getSymbolByNode($part->expr) ;
			    $dataFlow->setValue($symbol) ;
			}
			
			//处理三元表达式
			if($part && $part->getType() == "Expr_Ternary"){
				BIFuncUtils::ternaryHandler($type, $part, $dataFlow) ;
			}
			
		}//else
		
		//处理完一条赋值语句，加入DataFlowMap
		if($type == "right"){
			$block->getBlockSummary()->addDataFlowItem($dataFlow);
		}
	}
	
	/**
	 * 处理foreach语句:
	 * foreach($_GET['id'] as $key => $value)
	 * 转为两条赋值：
	 * 		$key = $_GET
	 * 		$value = $_GET
	 * 即key和value全部被传染
	 * 存入block的summary中
	 * @param BasicBlock $block
	 * @param Node $node
	 */
	public function foreachHandler($block,$node){
		if($node->expr->getType() == "Expr_ArrayDimFetch"){
			// 处理$key
			if($node->keyVar != null){
				$keyFlow = new DataFlow() ;
				$keyFlow->setName(NodeUtils::getNodeStringName($node->keyVar)) ;
				$location = new ArrayDimFetchSymbol() ;
				$location->setValue($node->keyVar) ;
				$keyFlow->setLocation($location) ;
				$keyFlow->setValue($node->expr) ;
				$block->getBlockSummary()->addDataFlowItem($keyFlow) ;
			}
			
			//处理$value
			if($node->valueVar != null){
				$valueFlow = new DataFlow() ;
				$valueFlow->setName(NodeUtils::getNodeStringName($node->valueVar)) ;
				$location = new ArrayDimFetchSymbol() ;
				$location->setValue($node->valueVar) ;
				$valueFlow->setLocation($location) ;
				$valueFlow->setValue($node->expr) ;
				$block->getBlockSummary()->addDataFlowItem($valueFlow) ;
			}

		}
	}
	
	
	/**
	 * 处理赋值的concat语句，添加至基本块摘要中
	 * @param AST $node
	 * @param BasicBlock $block
	 * @param string $type
	 */
	private function assignConcatHandler($node,$block,$dataFlow,$type){
		$this->assignHandler($node, $block,$dataFlow,$type) ;	
	}
	
	/**
	 * 处理常量，将常量添加至基本块摘要中
	 * @param AST $node
	 * @param BasicBlock $block
	 * @param string $mode 常量的模式：define const
	 */
	private function constantHandler($node,$block,$mode){
		if($mode == "define"){
			$cons = new Constants() ;
			if (count($node->args) > 1){
			    $cons->setName($node->args[0]->value->value) ;
                $cons->setValue($node->args[1]->value->value) ;
                $block->getBlockSummary()->addConstantItem($cons);
			}		
		}
		
		if($mode == "const"){
			$cons = new Constants() ;
			$cons->setName($node->consts[0]->name) ;
			$cons->setValue($node->consts[0]->value) ;
			$block->getBlockSummary()->addConstantItem($cons) ;
		}
	
	}
	
	/**
	 * 处理全局变量的声明，加入至摘要中
	 * @param Node $node
	 * @param BasicBlock $block
	 */
	private function globalDefinesHandler($node,$block){
		$globalDefine = new GlobalDefines() ;
		$globalDefine->setName(NodeUtils::getNodeStringName($node->value)) ;
		$block->getBlockSummary()->addGlobalDefineItem($globalDefine) ;
	}
	
	/**
	 * 将基本块中的return提取出来
	 * @param Node $node
	 * @param BasicBlock $block
	 */
	private function returnValueHandler($node,$block){
		$returnValue = new ReturnValue() ;
		$returnValue->setValue($node->expr) ;
		$block->getBlockSummary()->addReturnValueItem($returnValue) ;
	}
	
	/**
	 * 获取全局变量的注册信息
	 * @param Node $node
	 * @param BasicBlock $block
	 */
	private function registerGlobalHandler($node,$block){
		$funcName = NodeUtils::getNodeFunctionName($node);  //获取方法调用时的方法名		
		if($funcName != 'extract' and $funcName != "import_request_variables"){
			return ;
		}
		
		switch ($funcName){
			case 'extract':
				$registerItem = new RegisterGlobal() ;
				//extract只有在EXTR_OVERWRITE时才能在URL覆盖
				if(count($node->args) > 1 && $node->args[1]->value->name->parts[0] == "EXTR_OVERWRITE"){
					$registerItem->setIsUrlOverWrite(true) ;
				}else{
					$registerItem->setIsUrlOverWrite(false) ;
				}
				$varName = NodeUtils::getNodeStringName($node->args[0]->value);
				$registerItem->setName($varName) ;
				$block->getBlockSummary()->addRegisterGlobalItem($registerItem) ;
				break ;
				
			case 'import_request_variables':
				$registerItem = new RegisterGlobal() ;
				$varName = NodeUtils::getNodeStringName($node->args[0]->value);
				$registerItem->setName($varName) ;
				$registerItem->setIsUrlOverWrite(true) ;
				$block->getBlockSummary()->addRegisterGlobalItem($registerItem) ;
				break ;	
		}
	}
	
	
	/**
	 * 检测GLOBALS的定义
	 * @param Node $node
	 * @param BasicBlock $block
	 */
	private function registerGLOBALSHandler($node,$block){
	    $registerItem = new RegisterGlobal() ;
	    $varName = NodeUtils::getNodeGLOBALSNodeName($node);
	    $registerItem->setName($varName) ;
	    $registerItem->setIsUrlOverWrite(false) ;
	    $block->getBlockSummary()->addRegisterGlobalItem($registerItem) ;
	}
	
	/**
	 * 处理用户自定义函数
	 * @param Node $nodes  方法调用node
	 * @param BasicBlock $block  当前基本块
	 * @return array(position) 返回危险参数的位置
	 */
	private function sinkFunctionHandler($node,$block,$parentBlock){
	    global $scan_type;
		//对函数体的代码进行遍历并获取敏感参数的位置
		$parser = new PhpParser\Parser(new PhpParser\Lexer\Emulative) ;
		$traverser = new PhpParser\NodeTraverser;
		$visitor = new FunctionVisitor() ;
		$visitor->fileSummary = $this->fileSummary ;
		$visitor->block = $block ;
		$visitor->scan_type = $scan_type;
		$visitor->sinkContext = UserDefinedSinkContext::getInstance() ;
		$traverser->addVisitor($visitor) ;
		$traverser->traverse(array($node)) ;
		
		//获取函数的参数名列表：array(id,where)
		$del_arg_pos = NodeUtils::getNodeFuncParams($node) ;  
		
		//方法返回的数组
		$posArr = array();  
		
		//当变量无法跟踪到或变量被净化，返回null
		//$visitor->vars是敏感参数列表
		if((!$visitor->vars) || $visitor->vars == "safe"){
			return null;
		}
		foreach($del_arg_pos as $k => $v){
		    if(in_array($v,$visitor->vars)){
		        //$k+1：参数第一个记成1，而不是0
		        array_push($posArr, ($k+1)) ;
		    }
		}
		//将sink的类型拿到
		$posArr['type'] = $visitor->sinkType ;
		return $posArr;
	}
	
	
	/**
	 * 处理函数调用
	 * @param node $node 调用方法的节点
	 * @param BasicBlock $block 当前基本块
	 * @param fileSummary $fileSummary  当前文件摘要
	 */
	public function functionHandler($node, $block, $fileSummary){
	    //根据用户指定的扫描类型，查找相类型的sink函数
	    global $scan_type;
	    
	    //获取调用的函数名判断是否是sink调用
	    $funcName = NodeUtils::getNodeFunctionName($node);
	    //判断是否为sink函数,返回格式为array(true,funcname) or array(false)
	    $ret = NodeUtils::isSinkFunction($funcName, $scan_type);
	    if($ret[0] != null){
	        //如果发现了sink调用，启动污点分析
	        $analyser = new TaintAnalyser() ;
	        //获取危险参数的位置
	        $argPosition = NodeUtils::getVulArgs($node) ;
	        if(count($argPosition) == 0){
	            return ;
	        }
	    
	        //获取到危险参数位置的变量
	        $argArr = NodeUtils::getFuncParamsByPos($node, $argPosition);

	        //遍历危险参数名，调用污点分析函数
	        if(count($argArr) > 0){
	            foreach ($argArr as $item){
	                if(is_array($item)){
	                    foreach ($item as $v){
	                        $analyser->analysis($block, $node, $v, $fileSummary) ;
	                    }
	                }else{
	                    $analyser->analysis($block, $node, $item, $fileSummary) ;
	                }
	    
	            }
	            	
	        }
	    
	    }else{
	        //如果不是sink调用，启动过程间分析
	        $context = Context::getInstance() ;
            $funcBody = $context->getClassMethodBody(
            	$funcName,
            	$this->fileSummary->getPath(),
            	$this->fileSummary->getIncludeMap()
	        );
	        
			//check
	        if(!$funcBody || !is_object($funcBody)) return ;
			
	        if($funcBody->getType() == "Stmt_ClassMethod"){
	        	$funcBody->stmts = $funcBody->stmts[0] ;
	        }
	
	        //构建相应方法体的block和summary
	        $nextblock = $this->CFGBuilder($funcBody->stmts, NULL, NULL, NULL) ;

	        //ret危险参数的位置比如：array(0)
	        $ret = $this->sinkFunctionHandler($funcBody, $nextblock, $block);
	        if(!$ret){
	            return ;
	        }

	        //找到了array('del',array(0)) ;
	        $userDefinedSink = UserDefinedSinkContext::getInstance() ;

	        //$type应该从visitor中获取，使用$ret返回
	        $type = $ret['type'] ;
	        unset($ret['type']) ;

	        //加入用户sink上下文
	        $item = array($funcName,$ret) ;
	        $userDefinedSink->addByTagName($item, $type) ;
	    	        
	        //开始污点分析
            $argPosition = NodeUtils::getVulArgs($node) ;
            $argArr = NodeUtils::getFuncParamsByPos($node, $argPosition);	            

	        if(count($argArr) > 0){
	        	$analyser = new TaintAnalyser() ;
	        	foreach ($argArr as $item){
	        		if(is_array($item)){
	        			foreach ($item as $v){
	        				$analyser->analysis($block, $node, $v, $this->fileSummary) ;
	        			}
	        		}else{
	        			$analyser->analysis($block, $node, $item, $this->fileSummary) ;
	        		}
	        	  
	        	}
	        
	        }
	    }
	}
	/**
	 * 生成基本块摘要，为数据流分析做准备
	 * 1、处理赋值语句
	 * 2、记录全局变量定义
	 * 3、记录全局变量注册
	 * 4、记录返回值
	 * 5、记录常量定义
	 * @param BasicBlock $block
	 */
	public function simulate($block){
		//获取基本块中所有的节点
		$nodes = $block->getContainedNodes() ;
		//循环nodes集合，搜集信息加入到blocksummary中
		foreach ($nodes as $node){
		    if($node->getType() == 'Expr_ErrorSuppress'){
		        $node = $node->expr ;
		    }
			switch ($node->getType()){
				//处理赋值语句			
				case 'Expr_Assign':  
					$dataFlow = new DataFlow() ;
					$this->assignHandler($node, $block,$dataFlow,"left") ;
					$this->assignHandler($node, $block,$dataFlow,"right") ;
					break ;
				
				//处理foreach，转换为summary中的赋值
				case 'Stmt_Foreach':
					$this->foreachHandler($block, $node) ;
					break ;
				
				//处理字符串连接赋值
				//$sql .= "from users where"生成sql => "from users where"
				case 'Expr_AssignOp_Concat': 
					$dataFlow = new DataFlow() ;
					$this->assignConcatHandler($node, $block,$dataFlow,"left") ;
					$this->assignConcatHandler($node, $block,$dataFlow,"right") ;
					break ;
				
				//处理常量，加入至summary中
				//应该使用define判断
				case 'Expr_FuncCall' && (NodeUtils::getNodeFunctionName($node) == "define"):
					$this->constantHandler($node, $block,"define") ;
					break ;
				
				//处理const关键定义的常量
				case 'Stmt_Const':
					$this->constantHandler($node, $block,"const") ;
					break ;
				
				//处理全局变量的定义，global $a
				case 'Stmt_Global':
					$this->globalDefinesHandler($node, $block) ;
					break ;
					
				//过程内分析时记录
				case 'Stmt_Return':
					$this->returnValueHandler($node, $block) ;
					break ;
				
				//全局变量的注册extract,import_request_variables
				//识别净化值
				case 'Expr_FuncCall' && 
				     (NodeUtils::getNodeFunctionName($node) == "import_request_variables" || 
				     NodeUtils::getNodeFunctionName($node) == "extract") :
					$this->registerGlobalHandler($node, $block) ;
					break ;
					
				//如果$GLOBALS['name'] = 'xxxx' ;  则并入registerGlobal中
				case 'Expr_ArrayDimFetch' && (substr(NodeUtils::getNodeStringName($node),0,7) == "GLOBALS"):
				    $this->registerGLOBALSHandler($node, $block);
				    break;
				    
				//处理函数调用以及类方法的调用
				//过程间分析以及污点分析
				case 'Expr_MethodCall':
				case 'Expr_Include':
                case 'Expr_StaticCall':
				case 'Stmt_Echo':
				case 'Expr_Print':
				case 'Expr_FuncCall':
				case 'Expr_Eval':
					$this->functionHandler($node, $block, $this->fileSummary);
					break ;
				default:
				    $traverser = new PhpParser\NodeTraverser;
				    $visitor = new nodeFunctionVisitor() ;
				    $visitor->block = $block;
				    $visitor->fileSummary = $this->fileSummary;
				    $visitor->cfgGen = new CFGGenerator();
				    $traverser->addVisitor($visitor) ;
				    $traverser->traverse(array($node)) ;
				    break;
			}
		}
		
	}
	
	/**
	 * 由AST节点创建相应的CFG，用于后续分析
	 * 
	 * @param Node $nodes  传入的PHP file的所有nodes
	 * @param $condition   构建CFGNode时的跳转信息
	 * @param BasicBlock $pEntryBlock   入口基本块
	 * @param $pNextBlock   下一个基本块
	 */
	public function CFGBuilder($nodes,$condition,$pEntryBlock,$pNextBlock){
		//此文件的fileSummary
		global $JUMP_STATEMENT,$LOOP_STATEMENT,$STOP_STATEMENT,$RETURN_STATEMENT ;
		$currBlock = new BasicBlock() ;
		
		//创建一个CFG节点的边
		if($pEntryBlock){
			$block_edge = new CFGEdge($pEntryBlock, $currBlock,$condition) ;
			$pEntryBlock->addOutEdge($block_edge) ;
			$currBlock->addInEdge($block_edge) ;
		}
		//处理只有一个node节点，不是数组
        if (!is_array($nodes)){
            $nodes = array($nodes);
        }
		//迭代每个AST node
		foreach($nodes as $node){
			//print_r($node) ;
			//搜集节点中的require include require_once include_once的PHP文件名称
			$this->fileSummary->addIncludeToMap(NodeUtils::getNodeIncludeInfo($node)) ;
			
			if(!is_object($node)) continue ;
			
			//不分析函数定义
			if($node->getType() == "Stmt_Function"){
				continue ;
			}
			
			//如果节点是跳转类型的语句
			if(in_array($node->getType(), $JUMP_STATEMENT)){
				//生成基本块的摘要
				$this->simulate($currBlock) ;

				$nextBlock = new BasicBlock() ;
				//对每个分支，建立相应的基本块
				$branches = $this->getBranches($node) ;
				foreach ($branches as $b){
					$this->CFGBuilder($b->nodes, $b->condition, $currBlock, $nextBlock)	;				
				}
				$currBlock = $nextBlock ;
				
			//如果节点是循环语句
			}elseif(in_array($node->getType(), $LOOP_STATEMENT)){  
				//加入循环条件
				$this->addLoopVariable($node, $currBlock) ;
				$this->simulate($currBlock) ;

				//处理循环体
				$nextBlock = new BasicBlock() ;
				$this->CFGBuilder($node->stmts, NULL, $currBlock, $nextBlock) ;
				$currBlock = $nextBlock ;
			
			//如果节点是结束语句 throw break continue
			}elseif(in_array($node->getType(), $STOP_STATEMENT)){
				$currBlock->is_exit = true ;
				break ;
			
			//如果节点是return
			}elseif(in_array($node->getType(),$RETURN_STATEMENT)){
				$currBlock->addNode($node) ;
				$this->simulate($currBlock) ;
				//print_r($currBlock->getBlockSummary()) ;
				return $currBlock ;
			}else{
				$currBlock->addNode($node);
				//print_r($currBlock->getBlockSummary()) ;
			}
		}
		
		$this->simulate($currBlock) ;
		
		//echo  "当前基本块:<br/>" ;
		//print_r($currBlock) ;
		//echo "前驱基本块：<br/>" ;
		//$analyser = new TaintAnalyser() ;
		//$analyser->getPrevBlocks($currBlock) ;
		//print_r($analyser->getPathArr()) ;
		
		if($pNextBlock && !$currBlock->is_exit){
			$block_edge = new CFGEdge($currBlock, $pNextBlock) ;
			$currBlock->addOutEdge($block_edge) ;
			$pNextBlock->addInEdge($block_edge) ;
		}
		
		return $currBlock ;
	}
	
}



/**
 * 跳转语句的分支结构类
 * @author Administrator
 *
 */
class Branch{
	public $condition ;   //跳转条件
	public $nodes ;       //包含的节点
	
	/**
	 * 构造函数
	 * @param $cond  跳转的条件
	 * @param $nodes 分支中携带的所有nodes
	 */
	public function __construct($cond, $nodes){
		$this->condition = array($cond) ;
		if(is_array($nodes)){
			$this->nodes = $nodes ;
		}else{
			$this->nodes = array($nodes) ;
		}
		
		
		//将跳转的条件也加入至nodes中
		if(is_array($this->condition)){
			foreach ($this->condition as $cond){
				array_unshift($this->nodes, $cond) ;
			}
		}else{
			array_unshift($this->nodes, $this->condition) ;
		}
	}
	
}

/**
 * 获取PHP File中所有的AST节点的访问者
 * @author Administrator
 *
 */
class MyVisitor extends PhpParser\NodeVisitorAbstract{
	private $nodes = array();
	
	public function beforeTraverse(array $nodes){
		$this->nodes = $nodes ;
	}
	
	//getter
	public function getNodes(){
		return $this->nodes ;
	}
	
}

/**
 * 用来遍历LogicalOr节点，并将所有的分支分离出来
 * @author Administrator
 *
 */
class BranchVisitor extends PhpParser\NodeVisitorAbstract{
	public $branches = array() ;
	/**
	 * 将or表达式的分支分离成分支数组
	 * @param $node  LogicalOr节点
	 * @return $branches 分支数组
	 */
	public function leaveNode(Node $node) {
		if($node instanceof PhpParser\Node\Expr\BinaryOp\LogicalOr){
			if(!($node->left instanceof PhpParser\Node\Expr\BinaryOp\LogicalOr) && 
			    !($node->right instanceof PhpParser\Node\Expr\BinaryOp\LogicalOr)){
			    
				array_push($this->branches,$node->left) ;
				array_push($this->branches,$node->right) ;
			}else{
				if(!($node->left instanceof PhpParser\Node\Expr\BinaryOp\LogicalOr)){
					array_push($this->branches,$node->left) ;
				}elseif(!($node->right instanceof PhpParser\Node\Expr\BinaryOp\LogicalOr)){
					array_push($this->branches,$node->right) ;
				}
			}
		}
	}
	
}


class nodeFunctionVisitor extends PhpParser\NodeVisitorAbstract{
    public $block;
    public $fileSummary;
    public $cfgGen;

    public function leaveNode(Node $node){
        //处理过程间代码，即调用的方法定义中的源码
        if(($node->getType() == 'Expr_FuncCall' ||
            $node->getType() == 'Expr_MethodCall' ||
            $node->getType() == 'Expr_StaticCall')){
            $this->cfgGen->functionHandler($node, $this->block, $this->fileSummary);
        }
    }
}


/**
 * 处理方法调用
 * @author Exploit
 *
 */
class FunctionVisitor extends PhpParser\NodeVisitorAbstract{

	public $posArr ;   //参数列表
	public $block ;  //当前基本块
	public $vars = array();    //返回的数据array()
	public $sinkType ;   //返回的sink类型
	public $sinkContext ;   // 当前sink上下文
	public $fileSummary ;
	public $scan_type;
	
	public function leaveNode(Node $node){
		//处理过程间代码，即调用的方法定义中的源码
	    if(($node->getType() == 'Expr_FuncCall' || 
		    $node->getType() == 'Expr_MethodCall' || 
		    $node->getType() == 'Expr_StaticCall')){
			//获取到方法的名称
			$nodeName = NodeUtils::getNodeFunctionName($node);
			$ret = NodeUtils::isSinkFunction($nodeName,$this->scan_type);
			//进行危险参数的辨别
			if($ret[0] == true){
				//处理系统内置的sink
				//找到了mysql_query
				$cfg = new CFGGenerator() ;
				
				//array(where)找到危险参数的位置
				$args = $ret[1];
				if (is_array($args[0])){
				    $args = $args[0];
				}
				$vars = $this->senstivePostion($node,$this->block,$args) ;  
				$type = TypeUtils::getTypeByFuncName($nodeName) ;
				
				if($vars){
					//返回处理结果，将多个相关变量位置返回
					$this->vars = array_merge($this->vars, $vars);
				}
				
				if($type){
					//返回sink类型
					$this->sinkType = $type ;
				}
			}elseif(array_key_exists($nodeName,$this->sinkContext->getAllSinks())){
			    //处理已经加入sinksContext用户自定义函数
				//处理用户定义的sink
				$type = TypeUtils::getTypeByFuncName($nodeName) ;
				if($type){
					//返回sink类型
					$this->sinkType = $type ;
				}
				
				$context = Context::getInstance() ;
				$funcName = NodeUtils::getNodeFunctionName($node);
			    $funcBody = $context->getClassMethodBody(
			        $funcName,
			        $this->fileSummary->getPath(),
			        $this->fileSummary->getIncludeMap()
			    );
			    
			    if(!$funcBody) break ;
			    $cfg = new CFGGenerator() ;
			    //$this->block->function[$nodeName]
			    $arr = $this->sinkContext->getAllSinks() ;
			    $arr = $arr[$nodeName] ;
			    foreach ($arr as $pos){
			        $argName = NodeUtils::getNodeFuncParams($node);
			        $argName = $argName[$pos] ;
			        $this->vars = $this->sinkMultiBlockTraceback($argName, $this->block,0);			        
			    }
			}else {
                ;
			}
		}
	}
	
	/**
	 * sink多块回溯
	 * @param string $argName
	 * @param BasicBlock $block
	 * @param flowNum 遍历过的flow数量
	 * @return array
	 */
	public function sinkMultiBlockTraceback($argName,$block,$flowsNum=0){
	    //print_r("enter sinkMultiBlockTraceback<br/>");
	    $mulitBlockHandlerUtils = new multiBlockHandlerUtils($block);
	    $blockList = $mulitBlockHandlerUtils->getPathArr();
	    
	    $flows = $block->getBlockSummary()->getDataFlowMap();
	    //当前块flows没有遍历完
	    if(count($flows) != $flowsNum)
	        return $this->sinkTracebackBlock($argName, $block, $flowsNum);
	    
	    if($blockList == null || count($blockList) == 0){
            return  array($argName);
	    }

	    if(!is_array($blockList[0])){
	        //如果不是平行结构
	        $flows = $block->getBlockSummary()->getDataFlowMap();
	        if(count($flows) == $flowsNum){
	            $block = $blockList[0];
	            $ret = $this->sinkTracebackBlock($argName, $block, 0);
	            return $ret;
	        }
	        $ret = $this->sinkTracebackBlock($argName, $block, 0);
	        return $ret;
	    }else{
	        //平行结构
	        //当遇到sink函数时回溯碰到平行结构，那么遍历平行结构，将所有危险的相关变量全部记录下来
	        $retarr = array();
	        foreach ($blockList[0] as $block){
	            $ret = array();
	            $ret = $this->sinkTracebackBlock($argName, $block, 0);
	            $retarr = array_merge($retarr, $ret);
	        }
	        return $retarr;
	        
	    }

	}
	
	/**
	 * 获取敏感sink的参数对应的危险参数
	 * 如 :mysql_query($sql)
	 * 返回sql
	 * @param Node $node
	 * @param BasicBlock $block
	 * @return Ambigous <multitype:, multitype:string >
	 */
	public function senstivePostion($node,$block, $args){
	    $ret = array();
	    //得到sink函数的参数位置(1)
	    //$args = array(0) ;  //1  => mysql_query
	    foreach($args as $arg){
	        //args[$arg-1] sinks函数的危险参数位置商量调整
	        if ($arg > 0){
	            $argNameStr = NodeUtils::getNodeStringName($node->args[$arg-1]) ;   //sql
	            $ret = $this->sinkMultiBlockTraceback($argNameStr ,$block,0);  //array(where,id)
	        }
	    }
	    return $ret ;
	}
	
	/**
	 * sink单块回溯
	 * @param string $argName
	 * @param BasicBlock $block
	 * @param flowNum 遍历过的flow数量
	 * @return array
	 */
	public function sinkTracebackBlock($argName,$block,$flowsNum){
	    $flows = $block->getBlockSummary()->getDataFlowMap();
	    //需要将遍历过的dataflow删除
	    $temp = $flowsNum;
	    while ($temp>0){
	        array_pop($flows);
	        $temp --;
	    }
	    //将块内数据流逆序，从后往前遍历
	    $flows = array_reverse($flows);
	
	    foreach($flows as $flow){
	        $flowsNum ++;
	        //trace back
	        if($flow->getName() == $argName){
	            //处理净化信息
	            if ($flow->getLocation()->getSanitization()){
	                return "safe";
	            }
	            //得到flow->getValue()的变量node
	            //$sql = $a . $b ;  =>  array($a,$b)
	            if($flow->getValue() instanceof ConcatSymbol){
	                $vars = $flow->getValue()->getItems();
	            }else{
	                $vars = array($flow->getValue()) ;
	            }
	            $retarr = array();
	            foreach($vars as $var){
	                $var = NodeUtils::getNodeStringName($var);
	                $ret = $this->sinkMultiBlockTraceback($var,$block,$flowsNum);
	                //变量经过净化，这不需要跟踪该变量
	                if ($ret == "safe"){
	                    $retarr = array_slice($retarr, array_search($var,$retarr));
	                }else{
	                    $retarr = array_merge($ret,$retarr) ;
	                }
	            }
	            return $retarr;
	        }
	        	
	    }
	    if ($argName instanceof Node){
	        $argName = NodeUtils::getNodeStringName($argName);
	        return array($argName);
	    }
	    return $this->sinkMultiBlockTraceback($argName, $block,$flowsNum);
	    
	}
}

// //扫描漏洞类型
// $scan_type = 'ALL';
// echo "<pre>" ;
// //从用户那接受项目路径
// // $project_path = 'C:/users/xyw55/Desktop/test/simple-log_v1.3.1/upload';
// // $allFiles = FileUtils::getPHPfile($project_path);
// // //初始化
// // $initModule = new InitModule() ;
// // $initModule->init($project_path) ;

// $cfg = new CFGGenerator() ;
// $visitor = new MyVisitor() ;
// $parser = new PhpParser\Parser(new PhpParser\Lexer\Emulative) ;
// $traverser = new PhpParser\NodeTraverser ;
// $path = CURR_PATH . '/test/test.php';
// $cfg->getFileSummary()->setPath($path);
// $code = file_get_contents($path);
// $stmts = $parser->parse($code) ;
// $traverser->addVisitor($visitor) ;
// $traverser->traverse($stmts) ;
// $nodes = $visitor->getNodes() ;
// $pEntryBlock = new BasicBlock() ;
// $pEntryBlock->is_entry = true ;
// $ret = $cfg->CFGBuilder($nodes, NULL, NULL, NULL) ;


?>