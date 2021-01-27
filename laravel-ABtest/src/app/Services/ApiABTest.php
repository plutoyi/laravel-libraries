<?php
/**
 * ab test.
 */

namespace App\Services;

use Illuminate\Support\Facades\Config;
use Cache;

class ApiABTest
{
    /**
     * 用户标识
     *
     * @var string
     */
    public $id = '';

    /**
     * 版本号
     *
     * @var string
     */
    public $version = '';

    /**
     * 用户分组信息
     *
     * @var array
     */
    protected static $generatedVariants = [];

    /**
     * __construct
     *
     * @param 
     */
    public function __construct($id, $version)
    {
        $this->serVersion($verion);
        $this->setId($id);
    }

    /**
     * 获取所有实验
     *
     * @return array
     */
    public function getExperiments($experiment = '')
    {
        $ab = Config::get('ab', []);
        $experiments = [];
        if(isset($ab['experiments']) && isset($ab['experiments'][$this->version])){
            $experiments = $ab['experiments'][$this->version];
        }
        return isset($experiments[$experiment]) ? true : array_keys($experiments);
    }

    /**
     * 获取对应实验的用户分组
     *
     * @return string
     */
    public function getVariant($experiment, $variant = '')
    {
        $userVariant = config('ab.experiments.' . $this->version . '.' . $experiment);
        if ($variant) {
            return array_key_exists($variant, $userVariant['variant']);
        }
        return $userVariant;
    }

    /**
     * 获取对应实验的用户分组所占权重
     *
     * @return string
     */
    public function getVariantPercent($userVariant)
    {
        $count = 0;
        foreach($userVariant as $key => $value){
            $count += $value;
            $userVariant[$key] = $count;
        }
        return $userVariant;
    }

    /**
     * 获取用户标识符
     *
     * @return string
     */
    public function getId($experiment)
    {
        return $this->getPrefix($experiment) . $this->id;
    }
    
    /**
     * 设置用户标识符
     *
     * @return array
     */
    public function setId($id)
    {
        $this->id = $id;
    }

    /**
     * 用户标识符前缀区分不同实验
     *
     * @return string
     */
    public function getPrefix($experiment)
    {
        if(!$experiment){
            return Config('ab.prefix');
        }
        return Config('ab.prefix') . str_replace("_", "", $this->version) . str_replace("_", "", $experiment);
    }

    /**
     * 生成用户所在分组
     *
     * @return string
     */
    public function generateExperimentVariant($experiment)
    {
        //用户分组信息
        $userVariant = $this->getVariant($experiment);
        //参与用户的百分比
        $percent = $userVariant['percent'];
        //具体分组的用户百分比
        $userVariantPercent = $this->getVariantPercent($userVariant['variant']);
        //用户id哈希值
        $hash = abs(crc32($this->getId($experiment)));
        //默认用户不参与
        $variant = -1;
        //非ABTest样本用户
        $mod = $hash % 10000 + 1;
        if ($mod >= $percent * 100) {
            $variant = -1;
            return $this->setExperimentVariant($experiment, $variant);
        }
        $mod = $hash % 100 + 1;
        $preValue = 0;
        foreach($userVariantPercent as $key => $value){
            ($mod > $preValue) && ($mod <= $value) && $variant = $key;
            $preValue = $value;
        }
        return $this->setExperimentVariant($experiment, $variant);
    }

    /**
     * 设置用户所在分组
     *
     * @return string
     */
    public function setExperimentVariant($experiment, $variant)
    {
        return static::$generatedVariants[] = ['key' => $experiment, 'value' => strval($variant)];
    }

    /**
     * 获取用户所有分组信息
     *
     * @return string
     */
    public function getExperimentVariants($experiment = '')
    {
        if(!static::$generatedVariants){
            $experiments = $this->getExperiments();
            foreach($experiments as $v){
                $this->generateExperimentVariant($v);
            };
        }
        if($experiment){
            foreach(static::$generatedVariants as $key => $value){
                if(isset($value['key']) && ($value['key'] == $experiment )){
                    return static::$generatedVariants[$key];
                }
            }
        }
        return static::$generatedVariants;
    }

    /**
     * 获取缓存用户分组信息
     *
     * @return string
     */
    public function getCacheExperimentVariants($experiment = '')
    {
        if(!($result = Cache::get($cacheKey = $this->getPrefix($experiment) . $this->id))){
            Cache::put($cacheKey, $result = $this->getExperimentVariants($experiment), 60*24);
        }
        return $result;
    }
    
}