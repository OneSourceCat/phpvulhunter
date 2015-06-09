# phpvulhunter
phpvulhunter是一款PHP源码自动化审计工具，通过这个工具，可以对一些开源CMS进行自动化的代码审计，并生成漏洞报告。
##安装
首先从github上进行获取：

```
git clone https://github.com/OneSourceCat/phpvulhunter
```

下载完成后，将工程目录放置于WAMP等PHP-Web运行环境中即可访问`main.php`：

```
http://127.0.0.1/phpvulhunter/main.php
```
##使用
搭建好环境，访问main.php后，效果如下：
![](http://7xjb22.com1.z0.glb.clouddn.com/mainpage.png)

有几个参数需要填写：
> * Project Path:需要扫描的工程绝对路径（文件夹）
> * File Path：需要扫描的文件绝对路径（文件或者文件夹）
> * Vuln Turp：扫描的漏洞类型，默认为ALL
> * Encoding：CMS的编码类型

如果需要扫描整个工程，则`Project Path`与`File Path`填写一致即可。对于大的工程，由于代码量较多且内部引用复杂，所以可能会占用较多的CPU资源、花费较长的时间才能扫描完成。

配置好参数之后，点击`scan`按钮即可进行扫描，扫描中效果如下：
![](http://7xjb22.com1.z0.glb.clouddn.com/scanning.png)
##扫描报告
扫描完成后，就会生成扫描报告，具体如下：
![](http://7xjb22.com1.z0.glb.clouddn.com/report1.png)
相关参数含义如下：
> * File Path:出现漏洞的文件绝对路径
> * Vlun Type:漏洞的类型
> * Sink Call:危险函数调用的位置
> * Sensitive Arg:最后跟踪到的危险参数

查看代码时，点击`Code Viewer`即可：
![](http://7xjb22.com1.z0.glb.clouddn.com/report2.png)
##关于Bug和维护
由于作者马上面临实习，单靠个人精力已无力继续维护下去，因此可能会在扫描中出现bug。如果你有兴趣和足够的精力继续扩展与修正，并有信心能够应对大量繁琐的调试与扩展工作，请联系下面的邮箱索要详细的设计与实现文档。

Exploit:exploitcat@foxmail.com

xyw55:xyw5255@163.com
