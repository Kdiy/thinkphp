ThinkPHP 5.0
===============
## 安装
```
cd think && composer install

```
## 第一个Hello World
~~~
- 在application目录下创建一个模块目录 web
- 对应的结构
application  WEB部署目录（或者子目录）
├─web           		模块目录
│  ├─controller			控制器目录
│  ├─view				视图目录(API接口不需要视图)
- 创建一个Index.php文件,对应 web/controller/Index.php
- 编写内容
```
<?php
namesapce app\web\controller;
use think\Controller;
class Index extends Controller
{
	// 正常输出
	public function index()
	{
		output_data('hellow world');  // {code:200,datas:"hellow world"}
	}
	// 输出行为操作失败
	public function error()
	{
		output_msg(false,'error');	// {code:255,datas:{error:"error"}}
	}
	// 输出分页数据
	public function lists()
	{
		$list = [];
		$total = 1;
		output_list($list,page($total));	// {code:200,datas:{list:[],page:{total_page:1,current_page:1,total_num:0}}}
	}
}


?>
```
~~~





[![Total Downloads](https://poser.pugx.org/topthink/think/downloads)](https://packagist.org/packages/topthink/think)
[![Latest Stable Version](https://poser.pugx.org/topthink/think/v/stable)](https://packagist.org/packages/topthink/think)
[![Latest Unstable Version](https://poser.pugx.org/topthink/think/v/unstable)](https://packagist.org/packages/topthink/think)
[![License](https://poser.pugx.org/topthink/think/license)](https://packagist.org/packages/topthink/think)

在ThinkPHP5的基础上拓展了一些基础方法和公共类


## 目录结构

初始的目录结构如下：

~~~
www  WEB部署目录（或者子目录）
├─application           应用目录
│  ├─common             公共模块目录（可以更改）
│  ├─command            自定义脚本目录（可以更改）
│  │  ├─create_model.php快速创建一个数据表的增删改查脚本
│  │  ├─release.php     项目发布前进行的框架性能优化
│  ├─behavior           跨域配置文件目录
│  │  ├─cors.php     	配置跨域文件
│  ├─library            用于存放model的目录 可使用M('name')方法new
│  │  ├─lib     		公用类库,使用L('dir/name') 如 L('sdk/upload')方法new
│  ├─module_name        模块目录
│  │  ├─config.php      模块配置文件
│  │  ├─common.php      模块函数文件
│  │  ├─controller      控制器目录
│  │  ├─model           模型目录
│  │  ├─view            视图目录
│  │  └─ ...            更多类库目录
│  │
│  ├─command.php        命令行工具配置文件
│  ├─common.php         公共函数文件
│  ├─config.php         公共配置文件
│  ├─route.php          路由配置文件
|  ├─function_inc.php   **系统共用方法库**
│  ├─tags.php           应用行为扩展定义文件
│  └─database.php       数据库配置文件
│
├─public                WEB目录（对外访问目录）
│  ├─index.php          入口文件
│  ├─router.php         快速测试文件
│  └─.htaccess          用于apache的重写
│
├─thinkphp              框架系统目录
│  ├─lang               语言文件目录
│  ├─library            框架类库目录
│  │  ├─think           Think类库包目录
│  │  └─traits          系统Trait目录
│  │
│  ├─tpl                系统模板目录
│  ├─base.php           基础定义文件
│  ├─console.php        控制台入口文件
│  ├─convention.php     框架惯例配置文件
│  ├─helper.php         助手函数文件
│  ├─phpunit.xml        phpunit配置文件
│  └─start.php          框架入口文件
│
├─extend                扩展类库目录
├─runtime               应用的运行时目录（可写，可定制）
├─vendor                第三方类库目录（Composer依赖库）
├─build.php             自动生成定义文件（参考）
├─composer.json         composer 定义文件
├─LICENSE.txt           授权说明文件
├─README.md             README 文件
├─think                 命令行入口文件
~~~

> router.php用于php自带webserver支持，可用于快速测试


## 版权信息

ThinkPHP遵循Apache2开源协议发布，并提供免费使用。

本项目包含的第三方源码和二进制文件之版权信息另行标注。

版权所有Copyright © 2006-2018 by ThinkPHP (http://thinkphp.cn)

All rights reserved。

ThinkPHP® 商标和著作权所有者为上海顶想信息科技有限公司。

更多细节参阅 [LICENSE.txt](LICENSE.txt)
