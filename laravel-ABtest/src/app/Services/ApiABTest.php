<?php
/**
 * ApiABTest
 * 
 * Created by PhpStorm.
 * User: yisong.yang
 * Date: 2018/7/25
 * Time: 20:54
 */
namespace App\Services\ApiServices\Traits;

use Config,Cache;

trait ApiABTest
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
     * 平台
     *
     * @var string
     */
    public $platform = 'ios';

    /**
     * 国家
     *
     * @var string
     */
    public $term = ['country' => 'US'];

    /**
     * 用户分组信息
     *
     * @var array
     */
    protected static $generatedVariants = [];

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
     * 设置版本号
     */
    public function setVersion($version)
    {
        $this->version = $version;
    }

    /**
     * 获取版本号
     */
    public function getVersion()
    {
        return $this->version;
    }

    /**
     * 设置平台
     */
    public function setPlatform($platform)
    {
        $this->platform = $platform;
    }

    /**
     * 获取平台
     */
    public function getPlatform()
    {
        return $this->platform;
    }

    /**
     * 设置其他条件
     */
    public function setTerm($key, $value)
    {
        $this->term[$key] = $value;
    }

    /**
     * 获取条件
     */
    public function getTerm()
    {
        return $this->term;
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
        if(isset($ab['experiments']) && isset($ab['experiments']['default'][$this->version])){
            $experiments = $ab['experiments']['default'][$this->version];
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
        $userVariant = config('ab.experiments.default.' . $this->version . '.' . $experiment);
        if ($variant) {
            return array_key_exists($variant, $userVariant['variant']);
        }
        $platformUserVariant = config('ab.experiments.' . $this->platform . '.' . $this->version . '.' . $experiment);
        return $platformUserVariant ? $platformUserVariant : $userVariant;
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
     * 用户标识符前缀区分不同实验
     *
     * @return string
     */
    public function getPrefix($experiment)
    {
        if(!$experiment){
            return Config('ab.prefix');
        }
        return Config('ab.prefix') . str_replace("_", "", $experiment);
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
        //其他条件判断
        if(($condition = array_shift(array_keys($this->term))) && isset($userVariant['term'][$condition])){
            foreach($userVariant['term'][$condition] as $key => $value){
                if(in_array($this->term[$condition], $value)){
                    return $this->setExperimentVariant($experiment, $key);
                }
            }
        }
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
            //$variant = -1;
            //return $this->setExperimentVariant($experiment, $variant);
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
        return static::$generatedVariants[] = ['key' => $experiment, 'value' => $variant];
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
        $cacheKey = $this->getPrefix($experiment) . $this->id;
        if(!($result = Cache::get($cacheKey))){
            $result = $this->getExperimentVariants($experiment);
            Cache::put($cacheKey, $result, 60*24);
        }
        return $result;
    }
    
}