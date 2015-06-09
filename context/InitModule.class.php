<?php
/**
 * 初始化模块，初始化全局上下文，fileSummaryContext
 * @author xyw55
 *
 */
class InitModule {
	
    /**
     * init模块方法
     * 用于对工程进行初始化
     * @param string $project_path
     */    
    public function init($project_path, $allFiles){
        $this->initContext($project_path);
        $context = Context::getInstance() ;
        $this->initFileSummaryContext($project_path, $allFiles) ;
    }
    /**
     * 初始化class finder上下文
     * @param string $project_path
     */    
    private function initContext($project_path){
        $finder = new  ClassFinder($project_path) ;
        $finder->getContext() ;
    }
    
    /**
     * 初始化fileSummaryContext
     * @param string $project_path
     */
	private function initFileSummaryContext($project_path, $allFiles){
	    global $project_path;
	    $fileName = str_replace('/', '_', $project_path);
	    $fileName = str_replace(':', '_', $fileName);
	    $serialPath = CURR_PATH . "/data/fileSummaryConetxtSerialData/" . $fileName;
	    if (!is_file($serialPath)){
	        //创建文件
	        $fileHandler = fopen($serialPath, 'w');
	        fclose($fileHandler);
	    }
	    //判断本地序列化文件中是否存在UserSanitizeFuncConetxt
	    if(($serial_str = file_get_contents($serialPath))!=''){
	        $fileSummaryMap = unserialize($serial_str) ;
	        $fileSummaryContext = FileSummaryContext::getInstance();
	        $fileSummaryContext->setFileSummaryMap($fileSummaryMap);
	        return ;
	    }
	    
	    //没有序列化，则获取fileSummary
		$fileSummaryContext = FileSummaryContext::getInstance();
		foreach ($allFiles as $fileAbsPath){
		    $ret = FileSummaryGenerator::getFileSummary($fileAbsPath);
		    if ($ret){
		        $fileSummaryContext->addSummaryToMap($ret);
		    }
		}
		//对FileSummaryContext进行序列化，加快下次读取速度
		$this->serializeContext($serialPath, $fileSummaryContext->getFileSummaryMap()) ;
	}
	/**
	 * 序列化方法
	 * @param string $path
	 * @param multitype $context
	 */	
	public function serializeContext($path, $context){
	    file_put_contents($path, serialize($context)) ;
	}
	

	
}

?>