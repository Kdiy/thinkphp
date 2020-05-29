<?php
namespace app\command;
use think\console\Command;
use think\console\Output;
use think\console\Input;
// 在项目发布时进行的性能优化

class release extends Command
{
    protected $command_name = 'release_app';
    
    protected function configure(){
        
        $this->setName($this->command_name)->setDescription('optimize application performance before application release');
    }
    
    public function execute(Input $input,Output $output)
    {
        $shell_path = APP_PATH.'../';
        $shell = [
            // 生成数据表字段缓存
            'optimize:schema' =>"cd $shell_path && php think optimize:schema",  
            // 生成配置缓存
            'optimize:config' =>"cd $shell_path && php think optimize:config"
        ];
        $result = [];
        foreach ($shell as $k=>$v){
            exec($v,$result);
            $output->write($v.$result[1]);
        }
        $output->write('-- end');        
    }
    
}