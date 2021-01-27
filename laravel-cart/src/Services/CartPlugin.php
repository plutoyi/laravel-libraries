<?php
namespace LaravelCart\Services;

use LaravelCart\Services\CartService;

class CartPlugin
{
    /**
     * CartService
     *
     * @var array
     */
    public $cartService;

    /**
     * __construct
     * 
     * @param CartService $cartService
     * @return void
     */
    public function __construct(CartService $cartService)
    {
        $this->cartService = $cartService;
    } 

    /**
     * 添加购物车之前
     *
     * @param  $itemKey
     * @return mixed
     */
    public function beforeAddItem($itemKey) 
    {

    }

    /**
     * 添加购物车之后
     *
     * @param  $itemKey
     * @return mixed
     */
    public function afterAddItem($itemKey) 
    {

    }

    /**
     * 更新购物车之前
     *
     * @param  $itemKey
     * @return mixed
     */
    public function beforeUpdateItem($itemKey) 
    {

    }

    /**
     * 更新购物车之后
     *
     * @param  $itemKey
     * @return mixed
     */
    public function afterUpdateItem($itemKey) 
    {

    }

    /**
     * 删除购物车之前
     *
     * @param  $itemKey
     * @return mixed
     */
    public function beforeDeleteItem($itemKey) 
    {

    }

    /**
     * 删除购物车之后
     *
     * @param  $itemKey
     * @return mixed
     */
    public function afterDeleteItem($itemKey) 
    {

    }

    /**
     * 刷新购物车之前
     *
     * @param  $cart
     * @return mixed
     */
    public function beforeRefreshCart($cart) 
    {

    }

    /**
     * 刷新购物车之后
     *
     * @param  $cart
     * @return mixed
     */
    public function afterRefreshCart($cart) 
    {

    }
        
}