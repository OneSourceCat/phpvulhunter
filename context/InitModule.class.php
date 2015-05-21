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
        //print_r('init start... <br/>');
        
        $this->initContext($project_path);
        //test
        $context = Context::getInstance() ;
        //print_r($context);
        
        $this->initFileSummaryContext($project_path, $allFiles) ;
        //test
        //$path = 'C:\Users\xyw55\Desktop\test\niubicms\NiubiCMS v1.3\upload\bbs\index.php';
        //$absPath = $path;
        //$ret = FileSummaryGenerator::getFileSummary($absPath);
        //print_r($ret);
        //print_r('init end...<br/>');
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
	    
	    //判断本地序列化文件中是否存在UserSanitizeFuncConetxt
	    $serialPath = CURR_PATH . '/data/fileSummaryConetxtSerialData';
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