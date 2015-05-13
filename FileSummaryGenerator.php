<?php

class FileSummaryGenerator {
    
    public static function getIncludeFilesDataFlows($fileSummary){
        //1.得到include files
        $includeFiles = $fileSummary->getIncludeMap();
        $currentFilePath = $fileSummary->getPath();
        //2.foreach() files
        $retFlows = array();
        foreach ($includeFiles as $rpath){
            $absPath = FileUtils::getAbsPath($currentFilePath, $rpath);
            //  查看是否在fileSummaryContext中
            //  得到DataFlows
            $fileSummaryContext = FileSummaryContext::getInstance();           
            $ret = $fileSummaryContext->findSummaryByPath($absPath);
            if ($ret){
                $dataFlows = $ret->getFlowsMap();
                $retFlows = array_merge($dataFlows, $retFlows);
            }else{
                $fileSummary = self::getFileSummary($absPath);
                if ($fileSummary)
                    $retFlows = array_merge($fileSummary->getFlowsMap(), $retFlows);
            }
        }
        //return all files dataFlows
        return $retFlows;
    }
    
    /**
     * 得到一个文件基本信息FileSummary，包括
     * @param string $absPath
     */
	public static function getFileSummary($absPath){
	    if (!$absPath)
	        return ;
	    $visitor = new MyVisitor() ;
	    $parser = new PhpParser\Parser(new PhpParser\Lexer\Emulative) ;
	    $traverser = new PhpParser\NodeTraverser ;
	    $code = file_get_contents($absPath);
	    $stmts = $parser->parse($code);
	    $traverser->addVisitor($visitor) ;
	    $traverser->traverse($stmts) ;
	    $nodes = $visitor->getNodes() ;
	    
	    $fileSummary = new FileSummary();
	    $fileSummary->setPath($absPath);
	    $fileSummaryGenerator = new FileSummaryGenerator();
	    $fileSummaryGenerator->simulate($nodes, $fileSummary);
	    return $fileSummary;
	}
	
	/**
	 * 得到该文件的dataFlows
	 * @param Nodes $nodes
	 */
	public function simulate($nodes, $fileSummary){
	    
	    //循环nodes集合，搜集信息加入到中
	    foreach ($nodes as $node){
	        //搜集节点中的require include require_once include_once的PHP文件名称
	        $fileSummary->addIncludeToMap(NodeUtils::getNodeIncludeInfo($node)) ;
	        
	        switch ($node->getType()){
	            //处理赋值语句
	            case 'Expr_Assign':
	                $dataFlow = new DataFlow() ;
	                $this->assignHandler($node, $dataFlow, "left") ;
	                $this->assignHandler($node, $dataFlow, "right") ;
	                //处理完一条赋值语句，加入DataFlowMap
	                $fileSummary->addDataFlow($dataFlow);
	                break ;
	    
	            //处理字符串连接赋值
	            //$sql .= "from users where"生成sql => "from users where"
	            case 'Expr_AssignOp_Concat':
	                $dataFlow = new DataFlow() ;
	                $this->assignConcatHandler($node, $dataFlow, "left") ;
	                $this->assignConcatHandler($node, $dataFlow, "right") ;
	                //处理完一条赋值语句，加入DataFlowMap
	                $fileSummary->addDataFlow($dataFlow);
	                break ;
	           default:
	               break;
	        }
	    }
	} 
	
	/**
	 * 处理赋值的assign语句，添加至dataFlows中
	 * @param AST $node
	 * @param DataFlow $dataFlow
	 * @param string $type
	 */
	public function assignHandler($node, $dataFlow, $type){
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
	    if ($part && SymbolUtils::isArrayDimFetch($part) && (substr(NodeUtils::getNodeStringName($part),0,7)=="GLOBALS")){
	        //加入dataFlow
	        $arr = new ArrayDimFetchSymbol() ;
	        $arr->setValue($part) ;
	        if($type == "left"){
	            $dataFlow->setLocation($arr) ;
	            $dataFlow->setName(NodeUtils::getNodeGLOBALSNodeName($part)) ;
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
	        //不属于已有的任何一个symbol类型,如函数调用
	        if($part && $part->getType() == "Expr_FuncCall"){
                if($type == "left"){
    	            $dataFlow->setLocation($arr) ;
    	            $dataFlow->setName(NodeUtils::getNodeStringName($part)) ;
    	        }else if($type == "right"){
                    //处理净化信息和编码信息
                    //SanitizationHandler::setSanitiInfo($part, $dataFlow, $this->fileSummary) ;
                    //EncodingHandler::setEncodeInfo($part, $dataFlow) ;
                }
	        }
	    }
	    
	}
	
	/**
	 * 处理赋值的concat语句，添加至dataFlows中
	 * @param AST $node
	 * @param DataFlow $dataFlow
	 * @param string $type
	 */
	private function assignConcatHandler($node, $dataFlow, $type){
	    $this->assignHandler($node, $dataFlow, $type) ;
	}
	
}



?>