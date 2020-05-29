<?php
namespace app\library\lib\sdk;

class query{
    /**
     * @desc 把where数组转换为sql字符串
     * @param array $condition      where条件
     * @param array $condition_or   or条件
     * @throws \Exception
     * @return string
     */
    public static function buildWhere(array $condition, array $condition_or = []) : string
    {
       
        if(!$condition){
            throw new \Exception('condition is required');
        }
        if(!is_array($condition) || !is_array($condition_or)){
            throw new \Exception('condition must be Array');
        }
        $sql = "";
        
        if($condition && $condition_or){
            $sql1 = self::buildCondtion($condition);
            $sql2 = self::buildCondtion($condition_or);
            $sql =  " ( $sql1 ) OR ( $sql2 ) ";
        }else{
            $sql = self::buildCondtion($condition);
        }
        return self::buildLast($sql);
    } 
    private static function buildCondtion(array $condition = []) : string
    {
        $sql_arr = [];
        foreach ($condition as $k=>$v){
            $str = " `$k` = $v ";
            array_push($sql_arr, $str);
        }
        return $condition ? join(' AND ',$sql_arr) : '';
    }
    private static function buildLast(string $sql) :string
    {
        return $sql ? " WHERE $sql " : "";
        
    }
    
    
    
    
}