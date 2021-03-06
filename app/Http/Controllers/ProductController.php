<?php

namespace App\Http\Controllers;

use App\Cart;
use App\Product;
use App\Order;

use Illuminate\Http\Request;
use Session;
use Auth;

class ProductController extends Controller
{
    public function getIndex()
    {
        $product = Product::all();
        //dd($product);
        return view('shop.index',['products' => $product]);
    }

    public function getAddToCart(Request $request, $id)
    {
        $product = Product::find($id);
        $oldCart = Session::has('cart') ? Session::get('cart') : null;
        $cart = new Cart($oldCart);
        $cart->add($product, $product->id);

        $request->session()->put('cart', $cart);
        //dd($request->session()->get('cart'));
        return redirect()->route('product.index');
    }

    public function getReduceByOne($id)
    {
        $oldCart = Session::has('cart') ? Session::get('cart') : null;
        $cart = new Cart($oldCart);
        $cart->reduceByOne($id);
        if (count($cart->items) > 0) {
            Session::put('cart', $cart);
        } else {
            Session::forget('cart');
        }
        return redirect()->route('product.shoppingCart');
    }

    public function getRemoveItem($id)
    {
        $oldCart = Session::has('cart') ? Session::get('cart') : null;
        $cart = new Cart($oldCart);
        $cart->removeItem($id);
        if (count($cart->items) > 0) {
            Session::put('cart', $cart);
        } else {
            Session::forget('cart');
        }
        return redirect()->route('product.shoppingCart');
    }

    public function getCart()
    {
        if (!Session::has('cart')) {
            return view('shop.shopping-cart');
        }
        $oldCart = Session::get('cart');
        $cart = new Cart($oldCart);
        return view('shop.shopping-cart', ['products' => $cart->items, 'totalPrice' => $cart->totalPrice]);
    }

    public function getCheckout()
    {
        if (!Session::has('cart')) {
            return view('shop.shopping-cart');
        }
        $oldCart = Session::get('cart');
        $cart = new Cart($oldCart);
        //dd($cart);
        $total = $cart->totalPrice;
        return view('shop.checkout', ['total' => $total]);
    }
    //переделать на отправку почты
    public function postCheckout(Request $request)
    {
        if (!Session::has('cart')) {
            return redirect()->route('product.shoppingCart');
        }
        $oldCart = Session::get('cart');
        $cart = new Cart($oldCart);
        //dd($cart);
        $total = $cart->totalPrice;
        //скрипт отправки почты
        try {
            $order = new Order();
            $order->cart = serialize($cart);
            $order->address = $request->input('address');
            $order->name = $request->input('name');

            Auth::user()->orders()->save($order);

        } catch(\Exception $e) {
            return redirect()->route('checkout')->with('error', $e->getMessage());
        }
        //удаление ключа из сессии
        Session::forget('cart');
        //return view('shop.checkout', ['total' => $total]);
        return redirect()->route('product.index')->with('success', 'Successfully purchased products!');
    }
}
