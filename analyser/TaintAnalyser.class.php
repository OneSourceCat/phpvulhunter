<?php

require_once 'SqliAnalyser.class.php';
require_once 'XssAnalyser.class.php';
require_once 'FileAffectAnalyser.class.php';
require_once 'FileAnalyser.class.php';
require_once 'CodeAnalyser.class.php';
require_once 'ExecAnalyser.class.php';
require_once 'HeaderAnalyser.class.php';
require_once 'IncludeAnalyser.class.php';
require_once 'LDPAAnalyser.class.php';
require_once 'XPathAnalyser.class.php';

use PhpParser\Node ;

/**
 * 用于污点分析的类
 * 污点分析的任务：
 * 	（1）从各个基本块摘要中找出危险参数的变化
 * 	（2）评估危险参数是否受到有效净化
 * 	（3）根据评估结果报告漏洞
 * @author Exploit
 *
 */
class TaintAnalyser {
	//方法getPrevBlocks返回的参数
	private $pathArr = array() ;
	//source数组（GET POST ...）
	private $sourcesArr = array() ;
	
	public function __construct(){
		$this->sourcesArr = Sources::getUserInput() ;
	}
	
	public function getPathArr() {
		return $this->pathArr;
	}
	
	
	/**
	 * 根据变量的节点返回变量的名称
	 * @param unknown $var
	 * @return Ambigous <base, NULL, string, unknown>
	 */
	public function getVarName($var){
		$types = array('ArrayDimFetchSymbol','ConstantSymbol','ValueSymbol','VariableSymbol') ;
		if(in_array(get_class($var), $types)){
			$varName = NodeUtils::getNodeStringName($var->getValue()) ;
		}else{
			$varName = NodeUtils::getNodeStringName($var) ;
		}
		return $varName ;
	}
	
	/**
	 * 根据变量列表，判断某个元素的类型
	 * 如：
	 * (1)$sql = "select * from user where uid=".$uid ;
	 * 那么uid为数值类型
	 * (2)$sql = "select * from user where uid='".$uid ."'" ;
	 * 那么uid为字符类型
	 * @param array $vars
	 */
	public function addTypeByVars(&$vars){
		$len = count($vars) ;
		//调整顺序
		if($len > 2){
			$item_1 = $vars[0] ;
			$item_2 = $vars[1] ;
			$vars[0] = $item_2 ;
			$vars[1] = $item_1 ;
			unset($item_1) ;
			unset($item_2) ;
		}
		
		//设置type
		for($i=0;$i<$len;$i++){
			//如果元素有前驱和后继
			if(($i - 1) >= 0 && ($i + 1) <= $len-1){
				$is_pre_value = $vars[$i-1] instanceof ValueSymbol ;
				$is_curr_var = !($vars[$i] instanceof ValueSymbol) ;
				$is_nex_value = $vars[$i+1] instanceof ValueSymbol ;
				
				//如果前驱后继都不是value类型或者当前symbol不是变量，则pass
				if(!$is_pre_value || !$is_nex_value || !$is_curr_var){
					continue ;
				}
				//判断是否被单引号包裹
				$is_start_with = CommonUtils::startWith($vars[$i-1]->getValue(), "'");
				$is_end_with = CommonUtils::endsWith($vars[$i+1]->getValue(), "'") ;
				if($is_start_with != -1 && $is_end_with != -1){
					$vars[$i]->setType("valueInt") ;
				}
			}else{
				//如果没有前驱和后继 ，即为开头和结尾,且为var类型，直接设为int
				if($vars[$i] instanceof VariableSymbol){
					$vars[$i]->setType("valueInt") ;
				}
			}
		}
	}
	
	
	/**
	 * 根据flow获取数据流中赋值的变量名列表
	 * @param unknown $flow
	 * @return multitype:NULL
	 */
	public function getVarsByFlow($flow){
		//获取flow中的变量数组
		if($flow->getValue() instanceof ConcatSymbol){
			$vars = $flow->getValue()->getItems();
		}else if($flow->getValue() instanceof MutipleSymbol){
			$vars = $flow->getValue()->getSymbols() ;
		}else{
		    if(!($flow->getValue() instanceof Symbol) && $flow->getValue() instanceof Node){
		        $symbol = SymbolUtils::getSymbolByNode($flow->getValue()) ;
		        $flow->setValue($symbol) ;
		    }
			$vars = array($flow->getValue()) ;
		}
		
		//设置var的type，如果有引号包裹为string，否则为数值型
		$this->addTypeByVars($vars) ;
		foreach ($vars as $key => $value){
		    if($value == null){
		        array_slice($vars, $key) ;
		    }
		}
		return $vars ;
	}
	
	
	/**
	 * 获取当前基本块的所有前驱基本块
	 * 逆序排列
	 * @param BasicBlock $block
	 * @return Array 返回前驱基本块集合$this->pathArr
	 * 使用该方法时，需要对类属性$this->pathArr进行初始化
	 */
	public function getPrevBlocks($currBlock){
		if($currBlock != null){
			$blocks = array() ;
			$edges = $currBlock->getInEdges();
			//如果到达了第一个基本块则返回
			if(!$edges) return $this->pathArr;
			
			foreach ($edges as $edge){
				array_push($blocks, $edge->getSource()) ;
			}
			
			if(count($blocks) == 1){
				//前驱的节点只有一个
				if(!in_array($blocks[0],$this->pathArr,true)){
					array_push($this->pathArr,$blocks[0]) ;
				}
			}else{
				//前驱节点有多个
				if(!in_array($blocks,$this->pathArr,true)){
					array_push($this->pathArr,$blocks) ;
				} 
			}
		
			//递归
			foreach($blocks as $bitem){
				if(!is_array($bitem)){
					$this->getPrevBlocks($bitem);
				}else{
					$this->getPrevBlocks($bitem[0]) ;
				}
				
			}
		
		}
	}
	
	
	/**
	 * 污点分析中，对当前基本块的探测
	 * @param BasicBlock $block  当前基本块
	 * @param Node $node  当前调用sink的node
	 * @param string $argName  危险参数的名称
	 * @param FileSummary $fileSummary 当前文件的文件摘要
	 * 
	 */
	public function currBlockTaintHandler($block,$node,$argName,$fileSummary, $flowNum=0){
	    $tempNum = $flowNum;
		//获取数据流信息
		$flows = $block->getBlockSummary() ->getDataFlowMap() ;
		$flows = array_reverse($flows); //逆序处理flows
		//将处理过的flow移除
        while ($tempNum){
            $tempNum --;
            array_shift($flows);
        }
		foreach ($flows as $flow){
		    $flowNum ++; 
			if($flow->getName() == $argName){
				//处理净化信息,如果被编码或者净化则返回safe
				//先对左边的变量进行查询
			    if(is_object($flow->getLocation())){
			        $target = $flow->getLocation() ;
			        $type = TypeUtils::getTypeByFuncName(NodeUtils::getNodeFunctionName($node)) ;
			        $encodingArr = $target->getEncoding() ;
			        $saniArr =  $target->getSanitization() ;
			        $res = $this->isSanitization($type, $target, $saniArr, $encodingArr) ;
			        if($res == true){
			            return "safe" ;
			        }
			    }
				
				//获取flow中的右边赋值变量
				//得到flow->getValue()的变量node
				//$sql = $a . $b ;  =>  array($a,$b)
				$vars = $this->getVarsByFlow($flow) ;
				foreach($vars as $var){
				    if($var instanceof ValueSymbol || !is_object($var)){
				        continue ;
				    } 		    
					$varName = $this->getVarName($var) ;
					//如果var右边有source项
					if(in_array($varName, $this->sourcesArr)){
// 					    $ret = $this->multiFileHandler($block, $varName, $node, $fileSummary) ;
// 					    if($ret == 'safe'){
// 					        continue ;
// 					    }
						//报告漏洞
						$path = $fileSummary->getPath() ;
						$this->report($path, $path, $node, $flow->getLocation(), $type) ;
						continue ;
					}else{				
						//首先进行文件夹的分析
						$this->multiFileHandler($block, $varName, $node, $fileSummary) ;
						//文件间分析失败，递归
						$this->currBlockTaintHandler($block, $node, $varName, $fileSummary, $flowNum) ;
					}
				}
			}
			
		}
	}
	
	
/**
	 * 处理多个block的情景
	 * @param BasicBlock $block 当前基本块
	 * @param string $argName 敏感参数名
	 * @param Node $node 调用sink的node 
	 * @param FileSummary $fileSummary 当前文件的文件摘要
	 */
	public function multiBlockHandler($block, $argName, $node, $fileSummary){
		if($this->pathArr){
			$this->pathArr = array() ;
		}
		
		$this->getPrevBlocks($block) ;
		$block_list = $this->pathArr ;

		//单基本块进入   算法停止
		if(empty($block_list)){
		    // 首先，在当前基本块中探测变量，如果有source和不完整的santi则报告漏洞
		    $this->currBlockTaintHandler($block, $node, $argName, $fileSummary) ;
		    return ;
		}
		
		!empty($block) && array_push($block_list, $block) ;
		
		foreach($block_list as $bitem){
		    //处理非平行结构的前驱基本块
		    if(!is_array($bitem)){
		        $flows = $bitem->getBlockSummary()->getDataFlowMap() ;
		        $flows = array_reverse($flows) ;
		        //如果flow中没有信息，则换下一个基本块
		        if($flows == null){
		            //找到新的argName
		            foreach ($block->getBlockSummary()->getDataFlowMap() as $flow){
		                if($flow->getName() == $argName){
		                    if(is_object($flow->getLocation())){
		                        $target = $flow->getLocation() ;
		                        $type = TypeUtils::getTypeByFuncName(NodeUtils::getNodeFunctionName($node)) ;
		                        $encodingArr = $target->getEncoding() ;
		                        $saniArr =  $target->getSanitization() ;
		                    
		                        $res = $this->isSanitization($type, $target, $saniArr, $encodingArr) ;
		                        if($res == true){
		                            return "safe" ;
		                        }
		                    }
		                    
		                    $vars = $this->getVarsByFlow($flow) ;
		                    
		                    foreach ($vars as $var){
		                        $varName = $this->getVarName($var) ; 
		                        //如果$varName 为source
		                        if(in_array($varName, $this->sourcesArr)){
		                            //报告漏洞
		                            $path = $fileSummary->getPath() ;
		                            $type = TypeUtils::getTypeByFuncName(NodeUtils::getNodeFunctionName($node)) ;
		                            $this->report($path, $path, $node, $flow->getLocation(),$type) ;
		                            continue ;
		                        }
		                        $this->multiBlockHandler($bitem, $varName, $node, $fileSummary) ;
		                    }
		                    return ;
		                }else{
		                    //在最初block中，argName没有变化则直接递归
		                    if($block_list == null){
		                        return ;
		                    }else{
		                        $this->multiBlockHandler($bitem, $argName, $node, $fileSummary) ;
                                return ;
		                    }
		    
		                }
		            }
		        }else{
		            //对于每个flow,寻找变量argName
		            foreach ($flows as $flow){
		                if($flow->getName() == $argName){
		                    //处理净化信息,如果被编码或者净化则返回safe
		                    //先对左边的变量进行查询
		                    if(is_object($flow->getLocation())){
		                        $target = $flow->getLocation() ;
		                        $type = TypeUtils::getTypeByFuncName(NodeUtils::getNodeFunctionName($node)) ;
		                        $encodingArr = $target->getEncoding() ;
		                        $saniArr =  $target->getSanitization() ;
		                        	
		                        $res = $this->isSanitization($type, $target, $saniArr, $encodingArr) ;
		                        if($res == true){
		                            return "safe" ;
		                        }
		                    }
		                    	
		                    //获取flow中的右边赋值变量
		                    //得到flow->getValue()的变量node
		                    //$sql = $a . $b ;  =>  array($a,$b)
		                    $vars = $this->getVarsByFlow($flow) ;
		                    foreach($vars as $var){
		                        if($var instanceof ValueSymbol){
		                            continue ;
		                        }
		    
		                        $varName = $this->getVarName($var) ;
		                        //如果var右边有source项
		                        if(in_array($varName, $this->sourcesArr)){
		                            //报告漏洞
		                            $path = $fileSummary->getPath() ;
		                            $this->report($path, $path, $node, $flow->getLocation(), $type) ;
		                            continue ;
		                        }else{
		                            //首先进行文件夹的分析
		                            //首先根据fileSummary获取到fileSummaryMap
		                            $fileSummaryMap = FileSummaryGenerator::getIncludeFilesDataFlows($fileSummary) ;
		                            $fileSummaryMap && $this->multiFileHandler(
		                                $bitem,
		                                $varName,
		                                $node,
		                                $fileSummaryMap
		                            ) ;
		    
		                            //文件间分析失败，递归
		                            !empty($block_list) && $this->multiBlockHandler(
		                                $bitem,
		                                $varName,
		                                $node,
		                                $fileSummary
		                            ) ;
		                        }
		                    }
                		    return ;
		                }
		            }
		    
		        }
		        	
		    }else if(is_array($bitem) && count($block_list) > 0){
		        $bitem = array_reverse($bitem) ;
		        //是平行结构
		        foreach ($bitem as $block_item){   
		            $flows = $block_item->getBlockSummary()->getDataFlowMap() ;
		            $flows = array_reverse($flows) ;

		            //如果flow中没有信息，则换下一个基本块
		            if($flows == null){
		                if($argName == 'key') echo "x3";
		                //找到新的argName
		                foreach ($block->getBlockSummary()->getDataFlowMap() as $flow){
		                    if($flow->getName() == $argName){
		                        //判断是否净化
		                        if(is_object($flow->getLocation())){
		                            $target = $flow->getLocation() ;
		                            $type = TypeUtils::getTypeByFuncName(NodeUtils::getNodeFunctionName($node)) ;
		                            $encodingArr = $target->getEncoding() ;
		                            $saniArr =  $target->getSanitization() ;
		                        
		                            $res = $this->isSanitization($type, $target, $saniArr, $encodingArr) ;
		                            if($res == true){
		                                return "safe" ;
		                            }
		                        }
		                        
		                        $vars = $this->getVarsByFlow($flow) ;

		                        foreach ($vars as $var){
		                            if($var instanceof ValueSymbol){
		                                continue ;
		                            }
		                            $varName = $this->getVarName($var) ;
		                            if(in_array($varName, $this->sourcesArr)){
		                                //报告漏洞
		                                $path = $fileSummary->getPath() ;
		                                $this->report($path, $path, $node, $flow->getLocation(),$type) ;
		                                continue ;
		                            }else{
		                                $this->multiBlockHandler($block_item, $varName, $node,$fileSummary) ;
		                            }
		                        }
		                        return ;
		                    }else{
		                        //在最初block中，argName没有变化则直接递归
		                        $this->multiBlockHandler($block_item, $argName, $node,$fileSummary) ;
                                return ;
		                    }
		                }
		            }else{
		                //对于每个flow,寻找变量argName
		                foreach ($flows as $flow){
		                    if($flow->getName() == $argName){
		                        //处理净化信息,如果被编码或者净化则返回safe
		                        //先对左边的变量进行查询
		                        if(is_object($flow->getLocation())){
		                            $target = $flow->getLocation() ;
		                            $type = TypeUtils::getTypeByFuncName(NodeUtils::getNodeFunctionName($node)) ;
		                            $encodingArr = $target->getEncoding() ;
		                            $saniArr =  $target->getSanitization() ;
		    
		                            $res = $this->isSanitization($type, $target, $saniArr, $encodingArr) ;
		                            if($res == true){
		                                return "safe" ;
		                            }
		                        }
	                               
		                        //获取flow中的右边赋值变量
		                        //得到flow->getValue()的变量node
		                        //$sql = $a . $b ;  =>  array($a,$b)
		                        $vars = $this->getVarsByFlow($flow) ;
		                        foreach($vars as $var){
		                            if($var instanceof ValueSymbol){
		                                continue ;
		                            }
		                            
		                            $varName = $this->getVarName($var) ;
		                            //如果var右边有source项,直接报告漏洞
		                            if(in_array($varName, $this->sourcesArr)){
		                                //报告漏洞
		                                $path = $fileSummary->getPath() ;
		                                $this->report($path, $path, $node, $flow->getLocation(),$type) ;
		                                continue ;
		                            }else{
		                                //首先进行文件夹的分析
		                                //首先根据fileSummary获取到fileSummaryMap
		                                $fileSummaryMap = FileSummaryGenerator::getIncludeFilesDataFlows($fileSummary) ;
		                                $fileSummaryMap && $this->multiFileHandler(
		                                    $block, 
		                                    $varName, 
		                                    $node, 
		                                    $fileSummaryMap
		                                ) ;
		                                	 
		                                //文件间分析失败，递归
		                                $ret = $this->multiBlockHandler(
		                                    $block_item, 
		                                    $varName, 
		                                    $node, 
		                                    $fileSummary
		                                ) ;
		                            }
		                        }
		                        return ;
		                    }
		                }
		            }
		        }
		    }
		}
		
	}
	
	
	/**
	 * 多文件间分析
	 * @param BasicBlock $block  当前基本块
	 * @param string $argName 漏洞变量
	 * @param Node $node 调用sink的node
	 * @param array $fileSummaryMap 要分析的require文件的summary的list
	 */
	public function multiFileHandler($block, $argName, $node, $fileSummary,$flowNum=0){
	    //首先根据fileSummary获取到fileSummaryMap
	    $fileSummaryMap = FileSummaryGenerator::getIncludeFilesDataFlows($fileSummary) ;
	    if (!$fileSummaryMap){
	        return ;
	    }
	    
	    $node_path = $fileSummary->getPath() ;
		foreach ($fileSummaryMap as $fsummary){
			if($fsummary instanceof FileSummary){
				$flows = $fsummary->getFlowsMap() ;
				$tempNum = $flowNum ;
				while ($tempNum){
				    $tempNum --;
				    array_shift($flows) ;
				}
				
				foreach ($flows as $flow){
				    $flowNum ++;
					if($flow->getName() == $argName){
						//处理净化信息,如果被编码或者净化则返回safe
						//被isSanitization函数取代
					    if(is_object($flow->getLocation())){
					        $target = $flow->getLocation() ;
					        $type = TypeUtils::getTypeByFuncName(NodeUtils::getNodeFunctionName($node)) ;
					        $encodingArr = $target->getEncoding() ;
					        $saniArr =  $target->getSanitization() ;
					        $res = $this->isSanitization($type, $target, $saniArr, $encodingArr) ;
					        if($res == true){
					            return "safe" ;
					        }
					    }
					
						//获取flow中的右边赋值变量
						//得到flow->getValue()的变量node
						//$sql = $a . $b ;  =>  array($a,$b)
						$vars = $this->getVarsByFlow($flow) ;
						foreach($vars as $var){
							$varName = $this->getVarName($var) ;
							//如果var右边有source项
							if(in_array($varName, $this->sourcesArr)){
								//报告漏洞
								$var_path = $fsummary->getPath() ;
								$this->report($node_path, $var_path, $node, $flow->getLocation(),$type) ;
							}
						}
					}
				}
			}
		}
	}
	
	
	/**
	 * 根据sink的类型、危险参数的净化信息列表、编码列表
	 * 判断是否是有效的净化
	 * 返回:
	 * 		(1)true		=>得到净化
	 * 		(2)false	=>没有净化
	 * 'XSS','SQLI','HTTP','CODE','EXEC','LDAP','INCLUDE','FILE','XPATH','FILEAFFECT'
	 * @param string $type 漏洞的类型，使用TypeUtils可以获取
	 * @param Symbol $var 危险参数
	 * @param array $saniArr 危险参数的净化信息栈
	 * @param array $encodingArr 危险参数的编码信息栈
	 */
	public function isSanitization($type,$var,$saniArr_obj,$encodingArr){
	    //整理saniArr
	    $saniArr = array() ;
	    if(is_object($saniArr_obj)){
	        foreach ($saniArr_obj as $value){
	            array_push($saniArr, $value->funcName) ;
	        }
	    }else{
	        $saniArr = $saniArr_obj ;
	    }
        
		$is_clean = null ;
		$var_type = '' ;
		if($var instanceof Node){
		    $var = SymbolUtils::getSymbolByNode($var);
		}
		if ($var instanceof ValueSymbol){
		    return true;
		}
		
		//如果symbol类型为int，直接返回安全true
		if(!in_array($var->getType(), array('string','valueInt'))){
		    return true ;
		}
		
		//根据不同的漏洞类型进行判断
		switch ($type){
			case 'SQLI':
				$sql_analyser = new SqliAnalyser() ;
				$is_clean = $sql_analyser->analyse($var,$saniArr, $encodingArr) ;
				break ;
			case 'XSS':
				$xss_analyser = new XssAnalyser() ;
				$is_clean = $xss_analyser->analyse($var, $saniArr, $encodingArr) ;
				break ;
			case 'HTTP':
				$http_analyser = new HeaderAnalyser() ;
				$is_clean = $http_analyser->analyse($var, $saniArr, $encodingArr) ;
				break ;
			case 'CODE':
				$code_analyser = new CodeAnalyser() ;
				$is_clean = $code_analyser->analyse($var, $saniArr, $encodingArr) ;
				break ;
			case 'EXEC':
				$exec_analyser = new ExecAnalyser() ;
				$is_clean = $exec_analyser->analyse($var, $saniArr, $encodingArr) ;
				break ;
			case 'LDAP':
				$ldap_analyser = new LDPAAnalyser() ;
				$is_clean = $ldap_analyser->analyse($var, $saniArr, $encodingArr) ;
				break ;
			case 'INCLUDE':
				$include_analyser = new IncludeAnalyser() ;
				$is_clean = $include_analyser->analyse($var, $saniArr, $encodingArr) ;
				break ;
			case 'FILE':
				$file_analyser = new FileAnalyser() ;
				$is_clean = $file_analyser->analyse($var, $saniArr, $encodingArr) ;
				break ;
			case 'XPATH':
				$xpath_analyser = new XPathAnalyser() ;
				$is_clean = $xpath_analyser->analyse($var, $saniArr, $encodingArr) ;
				break ;
			case 'FILEAFFECT':
				$file_affect_analyser = new FileAffectAnalyser() ;
				$is_clean = $file_affect_analyser->analyse($var, $saniArr, $encodingArr) ;
				break ;
		}
		return $is_clean ;
	}
	
	
	/**
	 * 污点分析的函数
	 * @param BasicBlock $block 当前基本块
	 * @param Node $node 当前的函数调用node
	 * @param string $argName 危险参数名
	 * @param FileSummary 当前文件摘要
	 */
    public function analysis($block, $node, $argName, $fileSummary){
	    //传入变量本身就是source
        $varName = substr($argName, 0, strpos($argName, '['));
        if(in_array($varName, $this->sourcesArr) || in_array($argName, $this->sourcesArr)){
	        //报告漏洞
	        $path = $fileSummary->getPath() ;
	        $type = TypeUtils::getTypeByFuncName(NodeUtils::getNodeFunctionName($node)) ;
	        $this->report($path, $path, $node, $argName ,$type) ;
	    }else{
	        $path = $fileSummary->getPath() ;
	        //获取前驱基本块集合并将当前基本量添加至列表
	        $this->getPrevBlocks($block) ;
	        $block_list = $this->pathArr ;
	        array_push($block_list, $block) ;
	        //多个基本块的处理
	        $this->pathArr = array() ;
	        $this->multiBlockHandler($block, $argName, $node, $fileSummary) ;
	        $this->multiFileHandler($block, $argName, $node, $fileSummary);
	    }
    }
	
	/**
	 * 报告漏洞的函数
	 * @param string $path 出现漏洞的文件路径
	 * @param Node $node 出现漏洞的node
	 * @param Node $var  出现漏洞的变量node
	 * @param string 漏洞的类型
	 */
	public function report($node_path, $var_path, $node, $var, $type){
// 		echo "<pre>" ;
// 		echo "有漏洞=====>". $type ."<br/>" ;
// 		echo "漏洞变量：<br/>" ;
// 		print_r($var) ;
// 		echo "漏洞节点：<br/>" ;
// 		print_r($node) ;
		
		//获取结果集上下文
		$resultContext = ResultContext::getInstance() ;
		
		//加入至上下文中
		$record = new Result($node_path, $var_path, $type, $node, $var) ;
		
		//如果存在记录则不添加，反之才添加
		if($resultContext->isRecordExists($record)){
			return ;
		}else{
			$resultContext->addResElement($record) ;
		}
	}
	
	
}
?>