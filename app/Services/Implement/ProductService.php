<?php

namespace App\Services\Implement;

use App\Services\ProductServiceInterface;
use App\Repositories\ProductRepositoryInterface;
use App\Repositories\ProductTypeRepositoryInterface;
use App\Repositories\UnitRepositoryInterface;
use App\Repositories\ProducerRepositoryInterface;
use App\Repositories\SupplierRepositoryInterface;
use App\Repositories\ProductPriceRepositoryInterface;
use App\Common\DateTimeHelper;
use App\Common\AuthHelper;
use App\Common\FilterHelper;
use DB;
use League\Flysystem\Exception;

class ProductService implements ProductServiceInterface
{
    private $user;
    private $table_name, $table_names;

    protected $productRepo, $productTypeRepo, $unitRepo
    , $producerRepo, $supplierRepo, $productPriceRepo;

    public function __construct(ProductRepositoryInterface $productRepo
        , ProductTypeRepositoryInterface $productTypeRepo
        , UnitRepositoryInterface $unitRepo
        , ProducerRepositoryInterface $producerRepo
        , SupplierRepositoryInterface $supplierRepo
        , ProductPriceRepositoryInterface $productPriceRepo)
    {
        $this->productRepo      = $productRepo;
        $this->productTypeRepo  = $productTypeRepo;
        $this->unitRepo         = $unitRepo;
        $this->producerRepo     = $producerRepo;
        $this->supplierRepo     = $supplierRepo;
        $this->productPriceRepo = $productPriceRepo;

        $jwt_data = AuthHelper::getCurrentUser();
        if ($jwt_data['status']) {
            $user_data = AuthHelper::getInfoCurrentUser($jwt_data['user']);
            if ($user_data['status'])
                $this->user = $user_data['user'];
        }

        $this->table_name  = 'product';
        $this->table_names = 'products';
    }

    public function readAll()
    {
        $all = $this->productRepo->findAllSkeleton();

        $product_types = $this->productTypeRepo->findAllActive();
        $units         = $this->unitRepo->findAllActive();
        $producers     = $this->producerRepo->findAllActive();
        $suppliers     = $this->supplierRepo->findAllActive();

        return [
            $this->table_names => $all,
            'product_types'    => $product_types,
            'units'            => $units,
            'producers'        => $producers,
            'suppliers'        => $suppliers,
            'dis_or_sup'       => $this->user->dis_or_sup,
            'placeholder_code' => $this->productRepo->generateCode('PRODUCT')
        ];
    }

    public function readOne($id)
    {
        $one = $this->productRepo->findOneSkeleton($id);

        return [
            $this->table_name => $one
        ];
    }

    public function createOne($data)
    {
        $validates = $this->validateInput($data);
        if (!$validates['status'])
            return $validates;

        $result = [
            'status' => false,
            'errors' => []
        ];
        try {
            DB::beginTransaction();

            $i_one = [
                'code'            => $this->productRepo->generateCode('PRODUCT'),
                'barcode'         => $data['barcode'],
                'name'            => $data['name'],
                'description'     => $data['description'],
                'created_date'    => date('Y-m-d H:i:s'),
                'updated_date'    => null,
                'active'          => true,
                'product_type_id' => $data['product_type_id'],
                'producer_id'     => $data['producer_id'],
                'unit_id'         => $data['unit_id'],
                'is_allowed'      => in_array($this->user->position_id, [1, 2]) ? $data['is_allowed'] : false,
                'created_by'      => $this->user->id,
                'updated_by'      => 0
            ];

            $one = $this->productRepo->createOne($i_one);

            if (!$one) {
                DB::rollback();
                return $result;
            }

            // Insert ProductPrice
            $i_two = [
                'product_id'   => $one->id,
                'price_input'  => $data['price_input'],
                'price_output' => $data['price_output'],
                'created_date' => date('Y-m-d H:i:s'),
                'updated_date' => null,
                'active'       => true
            ];

            $two = $this->productPriceRepo->createOne($i_two);

            if (!$two) {
                DB::rollback();
                return $result;
            }

            DB::commit();
            $result['status'] = true;
            return $result;
        } catch (Exception $ex) {
            DB::rollBack();
            return $result;
        }
    }

    public function updateOne($data)
    {
        $validates = $this->validateInput($data);
        if (!$validates['status'])
            return $validates;

        $result = [
            'status' => false,
            'errors' => []
        ];
        try {
            DB::beginTransaction();

            $one = $this->productRepo->findOneActive($data['id']);

            $i_one = [
                'code'            => $this->productRepo->generateCode('PRODUCT'),
                'barcode'         => $data['barcode'],
                'name'            => $data['name'],
                'description'     => $data['description'],
                'updated_date'    => date('Y-m-d H:i:s'),
                'active'          => true,
                'product_type_id' => $data['product_type_id'],
                'producer_id'     => $data['producer_id'],
                'unit_id'         => $data['unit_id'],
                'is_allowed'      => in_array($this->user->position_id, [1, 2]) ? $data['is_allowed'] : false,
                'updated_by'      => $this->user->id
            ];

            $one = $this->productRepo->updateOne($one, $i_one);

            if (!$one) {
                DB::rollback();
                return $result;
            }

            // Update ProductPrice
            $two = $this->productPriceRepo->findOneActiveByFieldName('product_id', $one->id);

            $i_two = [
                'price_input'  => $data['price_input'],
                'price_output' => $data['price_output'],
                'updated_date' => date('Y-m-d H:i:s'),
                'active'       => true
            ];

            $two = $this->productPriceRepo->updateOne($two, $i_two);

            if (!$two) {
                DB::rollback();
                return $result;
            }

            DB::commit();
            $result['status'] = true;
            return $result;
        } catch (Exception $ex) {
            DB::rollBack();
            return $result;
        }
    }

    public function deactivateOne($id)
    {
        $result = [
            'status' => false,
            'errors' => []
        ];
        try {
            DB::beginTransaction();

            $one = $this->productRepo->deactivateOne($id);

            if (!$one) {
                DB::rollback();
                return $result;
            }

            // Deactivate ProductPrice
            $two = $this->productPriceRepo->findOneActiveByFieldName('product_id', $id);
            $two = $this->productPriceRepo->deactivateOne($two->id);

            if (!$two) {
                DB::rollback();
                return $result;
            }

            DB::commit();
            $result['status'] = true;
            return $result;
        } catch (Exception $ex) {
            DB::rollBack();
            return $result;
        }
    }

    public function deleteOne($id)
    {
        $result = [
            'status' => false,
            'errors' => []
        ];
        try {
            DB::beginTransaction();

            $one = $this->productRepo->destroyOne($id);

            if (!$one) {
                DB::rollback();
                return $result;
            }

            // Delete ProductPrice
            $two = $this->productPriceRepo->findOneActiveByFieldName('product_id', $id);
            $two = $this->productPriceRepo->destroyOne($two->id);

            if (!$two) {
                DB::rollback();
                return $result;
            }

            DB::commit();
            $result['status'] = true;
            return $result;
        } catch (Exception $ex) {
            DB::rollBack();
            return $result;
        }
    }

    public function searchOne($filter)
    {
        $from_date   = $filter['from_date'];
        $to_date     = $filter['to_date'];
        $range       = $filter['range'];
        $producer_id = $filter['producer_id'];
        $unit_id     = $filter['unit_id'];
        $barcode     = $filter['barcode'];
        $name        = $filter['name'];

        $filtered = $this->productRepo->findAllSkeleton();

        $filtered = FilterHelper::filterByFromDateToDate($filtered, 'created_at', $from_date, $to_date);

        $filtered = FilterHelper::filterByRangeDate($filtered, 'created_at', $range);

        $filtered = FilterHelper::filterByValue($filtered, 'producer_id', $producer_id);
        $filtered = FilterHelper::filterByValue($filtered, 'unit_id', $unit_id);
        $filtered = FilterHelper::filterByValue($filtered, 'barcode', $barcode);
        $filtered = FilterHelper::filterByValue($filtered, 'name', $name);

        return [
            $this->table_names => $filtered
        ];
    }

    /** ===== VALIDATE BASIC ===== */
    public function validateInput($data)
    {
        if (!$this->validateEmpty($data))
            return ['status' => false, 'errors' => 'Dữ liệu không hợp lệ.'];

        $msgs = $this->validateLogic($data);
        return $msgs;
    }

    public function validateEmpty($data)
    {
        if (!$data['name']) return false;
        if (!$data['producer_id']) return false;
        if (!$data['unit_id']) return false;
        if (!is_numeric($data['price_input'])) return false;
        if (!is_numeric($data['price_output'])) return false;
        return true;
    }

    public function validateLogic($data)
    {
        $msg_error = [];

        $skip_id = isset($data['id']) ? [$data['id']] : [];

        if ($data['barcode'] && $this->productRepo->existsValue('barcode', $data['barcode'], $skip_id))
            array_push($msg_error, 'Mã vạch sản phẩm đã tồn tại.');

        return [
            'status' => count($msg_error) > 0 ? false : true,
            'errors' => $msg_error
        ];
    }

    /** ===== VALIDATE ADVANCED ===== */
    public function validateUpdateOne($id)
    {
        return $this->validateDeactivateOne($id);
    }

    public function validateDeactivateOne($id)
    {
        $msg_error = [];

        return [
            'status' => count($msg_error) > 0 ? false : true,
            'errors' => $msg_error
        ];
    }

    public function validateDeleteOne($id)
    {
        return $this->validateDeactivateOne($id);
    }

    /** ===== MY FUNCTION ===== */

}