<?php
/**
 * 初始化模块，初始化全局上下文，fileSummaryContext
 * @author xyw55
 *
 */
class InitModule {
	
    
    public function init($project_path){
        $this->initContext($project_path);
        $this->initFileSummaryContext($project_path) ;
    }
    
    private function initContext($project_path){
        $finder = new  ClassFinder($project_path) ;
        $finder->getContext() ;
    }
    
    /**
     * 初始化fileSummaryContext
     * @param string $project_path
     */
	private function initFileSummaryContext($project_path){
	    
	    //判断本地序列化文件中是否存在UserSanitizeFuncConetxt
	    $serialPath = '/data/fileSummaryConetxtSerialData';
	    if(($serial_str = file_get_contents(CURR_PATH . $serialPath))!=''){
	        $fileSummaryMap = unserialize($serial_str) ;
	        $fileSummaryContext = FileSummaryContext::getInstance();
	        $fileSummaryContext->setFileSummaryMap($fileSummaryMap);
	        return ;
	    }
	    
	    //没有序列化，则获取fileSummary
		$allFiles = FileUtils::getPHPfile($project_path);
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
	
	public function serializeContext($path, $context){
	    file_put_contents(CURR_PATH . $path, serialize($context)) ;
	}
	

	
}

?>