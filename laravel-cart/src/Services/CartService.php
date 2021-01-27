<?php
namespace LaravelCart\Services;

use LaravelCart\Services\CartItemService;
use Carbon\Carbon;

class CartService
{
    /**
     * cartStatus
     *
     * @var array
     */
    protected $cartStatus = [];

    /**
     * cart
     *
     * @var string
     */
    protected $cart = '';

    /**
     * 插件列表
     *
     * @var array
     */
    public $pluginList = [];

    /**
     * CartItemService
     *
     * @var string
     */
    public $cartItemService;

    /**
     * __construct
     * 
     * @return void
     */
    public function __construct(CartItemService $cartItemService)
    {
        $this->cartItemService = $cartItemService;
    } 

    /**
     * 设置cartStatus数据
     *
     * @param object|array $cart
     * @return void
     */
    public function setCartStatus($cartStatus)
    {
        $this->cartStatus = $cartStatus;
        return $this;
    }

    /**
     * 获取cartStatus数据
     *
     * @return object
     */
    public function getCartStatus()
    {
        return $this->cartStatus;
    }

    /**
     * 获取cart数据
     *
     * @return object
     */
    public function getCart()
    {
        return $this->cart;
    }

    /**
     * 设置cart数据
     *
     * @param objext $cart
     * @return void
     */
    public function setCart($cart)
    {
        $this->cart = $cart;
        return $this;
    }

    /**
     * 注册插件
     *
     * @param string $name 插件名称
     * @param object $pluginInstance 插件实例
     * @return void
     */
    public function registerPlugin($name, $pluginInstance)
    {
        $name = ucfirst($name);
        if(!isset($this->pluginList[$name])){
            $this->pluginList[$name] = new $pluginInstance($this);
        }
        return $this;
    }

    /**
     * 获取当前插件列表
     *
     * @param null $pluginName
     * @return array|mixed
     */
    public function getPluginList($pluginName = null)
    {
        return $pluginName ? $this->pluginList[$pluginName] : $this->pluginList;
    }

    /**
     * 更新购物车记录
     *
     * @param array $product
     * @return mixed
     */
    public function update(array $product)
    {
        $this->cartItemService->setItems($this->cartItemService->getItems());
        if (! isset($product['id'])) {
            throw new Exception('id is required');
        }
        if (! $this->has($product['id'])) {
            throw new Exception('There is no item in shopping cart with id: ' . $product['id']);
        }
        $item = array_merge((array) $this->get($product['id']), $product);
        $items = $this->cartItemService->insert($item);
        $this->cartItemService->setItems($items);
        return $items;
    }

    /**
     * 更新购物车数量
     *
     * @param mixed $id
     * @param int $quantity
     *
     * @return mixed
     */
    public function updateQty($id, $quantity)
    {
        $item = (array) $this->get($id);
        $item['quantity'] = $quantity;
        return $this->update($item);
    }

    /**
     * 更新购物车价格
     *
     * @param mixed $id
     * @param float $price
     * @return mixed
     */
    public function updatePrice($id, $price)
    {
        $item = (array) $this->get($id);
        $item['price'] = $price;
        return $this->update($item);
    }

    /**
     * 获取购物车记录
     * @param int $id
     * @return array
     */
    public function get($id)
    {
        $this->cartItemService->setItems($this->cartItemService->getItems());
        return $this->cartItemService->findItem($id);
    }

    /**
     * 判断是否购物车是否有这个记录
     * @param int $id
     * @return boolean
     */
    public function has($id)
    {
        $this->cartItemService->setItems($this->cartItemService->getItems());
        return $this->cartItemService->findItem($id)? true : false;
    }

    /**
     * 添加购物车
     *
     * @param  array  $product
     * @return mixed
     */
    public function add(array $product)
    {
        $pluginList = $this->getPluginList();
        foreach($pluginList as $key => $plugin){
            $plugin->beforeAddItem($product);
        }
        $this->cartItemService->validateItem($product);
        if ($this->has($product['id'])) {
            $item = $this->get($product['id']);
            return $this->updateQty($item->id, $item->quantity + $product['quantity']);
        }
        $this->cartItemService->setItems($this->cartItemService->getItems());
        $items = $this->cartItemService->insert($product);
        foreach($pluginList as $key => $plugin){
            $plugin->afterAddItem($product);
        }
        return $this->cartItemService->getItems();
    }
}