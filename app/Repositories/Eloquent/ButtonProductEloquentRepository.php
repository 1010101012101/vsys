<?php

namespace App\Repositories\Eloquent;

use App\Repositories\ButtonProductRepositoryInterface;
use App\ButtonProduct;

class ButtonProductEloquentRepository extends BaseEloquentRepository implements ButtonProductRepositoryInterface
{
    /** ===== INIT MODEL ===== */
    public function setModel()
    {
        return ButtonProduct::class;
    }

    /** ===== PUBLIC FUNCTION ===== */
    public function findAllSkeleton()
    {
        return $this->model->where('button_products.active', true)
            ->leftJoin('products', 'products.id', '=', 'button_products.product_id')
            ->leftJoin('devices', 'devices.id', '=', 'button_products.button_id')
            ->leftJoin('io_centers', 'io_centers.id', '=', 'devices.io_center_id')
            ->leftJoin('devices as cabinets', 'cabinets.id', '=', 'devices.parent_id')
            ->leftJoin('distributors', 'distributors.id', '=', 'io_centers.dis_id')
            ->select('button_products.id', 'button_products.created_date', 'button_products.button_id', 'button_products.total_quantum', 'products.name as product_name'
                , 'devices.code as tray_code', 'devices.name as tray_name', 'devices.description as tray_description', 'devices.quantum_product as tray_quantum_product'
                , 'io_centers.id as io_center_id', 'io_centers.code as io_center_code', 'io_centers.name as io_center_name', 'io_centers.description as io_center_description'
                , 'cabinets.id as cabinet_id', 'cabinets.code as cabinet_code', 'cabinets.name as cabinet_name', 'cabinets.description as cabinet_description'
                , 'distributors.id as distributor_id', 'distributors.name as distributor_name'
                , 'products.id as product_id'
            )
            ->get();
    }

    public function findOneSkeleton($id)
    {
        return $this->findAllSkeleton()->where('button_products.id', $id)->first();
    }
}