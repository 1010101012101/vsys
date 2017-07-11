<?php

namespace App\Repositories\Eloquent;

use App\Repositories\ProductTypeRepositoryInterface;
use App\ProductType;

class ProductTypeEloquentRepository extends BaseEloquentRepository implements ProductTypeRepositoryInterface
{
    /** ===== INIT MODEL ===== */
    public function setModel()
    {
        return ProductType::class;
    }

    /** ===== PUBLIC FUNCTION ===== */
    public function findAllSkeleton()
    {
        return $this->findAllActive();
    }

    public function findOneSkeleton($id)
    {
        return $this->findAllSkeleton()->where('product_types.id', $id)->first();
    }
}