<?php 
define('CURR_PATH',str_replace("\\", "/", dirname(__FILE__))) ;

require_once CURR_PATH . '/vendor/autoload.php' ;
require_once CURR_PATH . '/BasicBlock.php';

require_once CURR_PATH . '/utils/AnalyseUtils.class.php';
require_once CURR_PATH . '/utils/FileUtils.class.php';
require_once CURR_PATH . '/utils/SymbolUtils.class.php';
require_once CURR_PATH . '/utils/NodeUtils.class.php';
require_once CURR_PATH . '/utils/TypeUtils.class.php';
require_once CURR_PATH . '/utils/multiBlockHandlerUtils.class.php';

require_once CURR_PATH . '/symbols/Symbol.class.php' ;
require_once CURR_PATH . '/symbols/ValueSymbol.class.php';
require_once CURR_PATH . '/symbols/VariableSymbol.class.php';
require_once CURR_PATH . '/symbols/MutipleSymbol.class.php';
require_once CURR_PATH . '/symbols/ArrayDimFetchSymbol.class.php';
require_once CURR_PATH . '/symbols/ConcatSymbol.class.php';
require_once CURR_PATH . '/symbols/ConstantSymbol.class.php';
require_once CURR_PATH . '/symbols/SanitizationHandler.class.php';
require_once CURR_PATH . '/symbols/EncodingHandler.class.php';

require_once CURR_PATH . '/summary/FileSummary.class.php';

require_once CURR_PATH . '/context/ClassFinder.php';
require_once CURR_PATH . '/context/UserDefinedSinkContext.class.php';
require_once CURR_PATH . '/context/UserSanitizeFuncConetxt.php';
require_once CURR_PATH . '/context/InitModule.class.php';

require_once CURR_PATH . '/conf/sinks.php' ;
require_once CURR_PATH . '/conf/sources.php' ;
require_once CURR_PATH . '/conf/securing.php';

require_once CURR_PATH . '/analyser/TaintAnalyser.class.php';

require_once CURR_PATH . '/libs/Smarty.class.php';

header("Content-type:text/html;charset=utf-8") ;

ini_set('xdebug.max_nesting_level', 2000);


?>