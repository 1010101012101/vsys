<?php

namespace App\Repositories\Eloquent;

use App\Repositories\ProductPriceRepositoryInterface;
use App\ProductPrice;

class ProductPriceEloquentRepository extends BaseEloquentRepository implements ProductPriceRepositoryInterface
{
    /** ===== INIT MODEL ===== */
    public function setModel()
    {
        return ProductPrice::class;
    }

    /** ===== PUBLIC FUNCTION ===== */
    public function findAllSkeleton()
    {
        return $this->findAllActive();
    }

    public function findOneSkeleton($id)
    {
        return $this->findAllSkeleton()->where('product_prices.id', $id)->first();
    }
}