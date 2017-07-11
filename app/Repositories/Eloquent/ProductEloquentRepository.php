<?php

namespace App\Repositories\Eloquent;

use App\Repositories\ProductRepositoryInterface;
use App\Product;
use App\Common\DBHelper;
use DB;

class ProductEloquentRepository extends BaseEloquentRepository implements ProductRepositoryInterface
{
    /** ===== INIT MODEL ===== */
    public function setModel()
    {
        return Product::class;
    }

    /** ===== PUBLIC FUNCTION ===== */
    public function findAllSkeleton()
    {
        return $this->model
            ->where('products.active', true)
            ->leftJoin('product_prices', 'product_prices.product_id', '=', 'products.id')
            ->leftJoin('product_types', 'product_types.id', '=', 'products.product_type_id')
            ->leftJoin('units', 'units.id', '=', 'products.unit_id')
            ->leftJoin('producers', 'producers.id', '=', 'products.producer_id')
            ->select('products.*'
                , 'product_prices.price_input'
                , DB::raw(DBHelper::getWithCurrencyFormat('product_prices.price_input', 'fc_price_input'))
                , 'product_prices.price_output'
                , DB::raw(DBHelper::getWithCurrencyFormat('product_prices.price_output', 'fc_price_output'))
                , 'product_types.name as product_type_name'
                , 'producers.name as producer_name'
                , 'units.name as unit_name'
            )
            ->get();
    }

    public function findOneSkeleton($id)
    {
        return $this->findAllSkeleton()->where('products.id', $id)->first();
    }
}