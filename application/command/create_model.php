<?php
namespace app\command;
use think\console\Command;
use think\console\Output;
use think\console\Input;


class create_model extends Command
{
    protected $command_name = 'create_model';
    
    protected function configure(){
       
        $this->setName($this->command_name)
        ->addArgument('name')->addArgument('desc')
        ->setDescription('为表创建一个新的model, 语法 php think create_model [name] [description]');
    }
    
    public function execute(Input $input,Output $output)
    {
        $params = $input->getArguments();
        $name = $params['name'];
        $desc = $params['desc'] ?? '';

$string = <<<EOT
<?php\r\n
namespace app\library;\r\n
// {$desc}
class {$name}
{
\r\n
    protected @tb0 = '{$name}';
    // del
 	public function _del(array @condtion)
 	{
 		return @this->_edit(@condtion,['deleted'=>1]);
 	}
	// rel del
 	public function _rdel(array @condtion)
 	{
 		return db(@this->tb0)->where(@condtion)->delete();
 	}
	
    // edit
 	public function _edit(array @condtion, array @data)
 	{
 	  return db(@this->tb0)->where(@condtion)->update(@data);
 	}
    // find
 	public function _find(array @condtion, @field = '*')
 	{
 	  return db(@this->tb0)->where(@condtion)->field(@field)->find();
 	}
    // list
 	public function _list(array @condtion, @page = 1, @limit = 10, @field = '*', @order = '')
 	{
 	  return db(@this->tb0)->where(@condtion)->field(@field)->page(@page)->limit(@limit)->order(@order)->select();
 	}
    // count
 	public function _listCount(array @condtion)
 	{
 	  return db(@this->tb0)->where(@condtion)->count();
 	}
 \r\n
 }\r\n
 ?>
EOT;
         
         $string = str_replace('@','$',$string);
         
         $filename = APP_PATH.'library/'.$name;
         
         $io = fopen($filename.'.php','w+');
         $res = fwrite($io,$string);
         fclose($io);


         $output->write($res);
    }
    
}


