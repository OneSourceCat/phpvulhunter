<?php
require_once '../vendor/autoload.php';
ini_set('xdebug.max_nesting_level', 2000);

use PhpParser\Node;

/**
 * 文件处理类
 * 
 * @author xyw55
 *        
 */
class FileUtils
{
    /**
     *
     * @param php项目文件夹路径 $dirpath            
     * @return array of phpfiles 
     */
    public static  function getPHPfile($dirpath)
    {
        $ret = array();
        if (! is_dir($dirpath))
            return $ret;
        $dh = opendir($dirpath);
        while (($file = readdir($dh)) != false) {
            // 文件名的全路径 包含文件名
            $filePath = $dirpath . "\\" . $file;
            // echo $filePath."<br/>";
            if ($file == "." or $file == "..")
                continue;
            elseif (is_dir($filePath)) {
                foreach (FileUtils::getPHPfile($filePath) as $filePath)
                    if (! is_null($filePath))
                        array_push($ret, $filePath);
            } elseif (substr($filePath, - 4) == ".php")
                array_push($ret, $filePath);
        }
        closedir($dh);
        return $ret;
    }

    /**
     * 通过判断文件中Nodes的数量和class node和 function node数量比较，找出main php files
     * @param  php项目文件夹路径$dirpath
     * @return multitype:
     */
    public static function mainFileFinder($dirpath)
    {
        $files = FileUtils::getPHPfile($dirpath);
        //print_r($files);
        $should2parser = array();
        $parser = new PhpParser\Parser(new PhpParser\Lexer\Emulative());      
        $traverser = new PhpParser\NodeTraverser();
        $visitor = new VisitorForLine();
        $traverser->addVisitor($visitor);
        foreach ($files as $file) {
            $code = file_get_contents($file);
            try {
                $stmts = $parser->parse($code);
            } catch (PhpParser\Error $e) {
                //die("==> Parse Error: {$e->getMessage()}\n");
                print_r("==> Parse Error: {$e->getMessage()}\n");
            }
            $traverser->traverse($stmts);
            $nodes = $visitor->getNodes();
            $sumcount = count($nodes);
            $count = $visitor->getCount();
            echo "sum:$sumcount------count:$count-------$file<br/>";
            //暂时确定当比值小于0.6，为main php files
            if($count/$sumcount<0.6)
                array_push($should2parser, $file);
            $visitor->setCount(0);
            $visitor->setNodes(array());
        }
       return $should2parser;
    }
}

/**
 *
 * @author xyw55
 *        
 */
class VisitorForLine extends PhpParser\NodeVisitorAbstract
{
    private $nodes = array();
    private $count = 0;
    public function beforeTraverse(array $nodes)
    {
        $this->nodes = $nodes;
    }
    public function enterNode(Node $node)
    {
        $type = $node->getType();
        // echo $type;
        switch ($type) {
            case "Stmt_Class":
                $this->count= $this->count+2;
                break;
            case "Stmt_Function":
                $this->count= $this->count+1;
                break;
            default:
                ;
                break;
        }
    }
	/**
     * @return the $nodes
     */
    public function getNodes()
    {
        return $this->nodes;
    }

	/**
     * @return the $count
     */
    public function getCount()
    {
        return $this->count;
    }

	/**
     * @param multitype: $nodes
     */
    public function setNodes($nodes)
    {
        $this->nodes = $nodes;
    }

	/**
     * @param number $count
     */
    public function setCount($count)
    {
        $this->count = $count;
    }
}
echo "<pre>";
$dirpath = "F:\wamp\www\discuz7.2\upload";
$should2parser = FileUtils::mainFileFinder($dirpath);
print_r($should2parser);
?>