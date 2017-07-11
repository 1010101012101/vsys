<?php

namespace App\Services\Implement;

use App\Services\SupplierServiceInterface;
use App\Repositories\SupplierRepositoryInterface;
use App\Common\DateTimeHelper;
use App\Common\AuthHelper;
use App\Common\FilterHelper;
use DB;
use League\Flysystem\Exception;

class SupplierService implements SupplierServiceInterface
{
    private $user;
    private $table_name, $table_names;

    protected $supplierRepo;

    public function __construct(SupplierRepositoryInterface $supplierRepo)
    {
        $this->supplierRepo = $supplierRepo;

        $jwt_data = AuthHelper::getCurrentUser();
        if ($jwt_data['status']) {
            $user_data = AuthHelper::getInfoCurrentUser($jwt_data['user']);
            if ($user_data['status'])
                $this->user = $user_data['user'];
        }

        $this->table_name  = 'supplier';
        $this->table_names = 'suppliers';
    }

    public function readAll()
    {
        $all = $this->supplierRepo->findAllSkeleton();

        return [
            $this->table_names => $all,
            'placeholder_code' => $this->supplierRepo->generateCode('SUPPLIER')
        ];
    }

    public function readOne($id)
    {
        $one = $this->supplierRepo->findOneSkeleton($id);

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
                'code'          => $data['code'] ? $data['code'] : $this->supplierRepo->generateCode('SUPPLIER'),
                'name'          => $data['name'],
                'address'       => $data['address'],
                'city_code'     => $data['city_code'],
                'district_code' => $data['district_code'],
                'ward_code'     => $data['ward_code'],
                'phone'         => $data['phone'],
                'email'         => $data['email'],
                'fax'           => $data['fax'],
                'note'          => $data['note'],
                'active'        => true
            ];

            $one = $this->supplierRepo->createOne($i_one);

            if (!$one) {
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

            $one = $this->supplierRepo->findOneActive($data['id']);

            $i_one = [
                'code'          => $data['code'] ? $data['code'] : $this->generateCode($this->class_name, 'SUPPLIER'),
                'name'          => $data['name'],
                'address'       => $data['address'],
                'city_code'     => $data['city_code'],
                'district_code' => $data['district_code'],
                'ward_code'     => $data['ward_code'],
                'phone'         => $data['phone'],
                'email'         => $data['email'],
                'fax'           => $data['fax'],
                'note'          => $data['note'],
                'active'        => true
            ];

            $one = $this->supplierRepo->updateOne($one, $i_one);

            if (!$one) {
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

            $one = $this->supplierRepo->deactivateOne($id);

            if (!$one) {
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

            $one = $this->supplierRepo->destroyOne($id);

            if (!$one) {
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
        $from_date     = $filter['from_date'];
        $to_date       = $filter['to_date'];
        $range         = $filter['range'];
        $supplier_id   = $filter['supplier_id'];
        $city_code     = $filter['city_code'];
        $district_code = $filter['district_code'];
        $ward_code     = $filter['ward_code'];

        $filtered = $this->supplierRepo->findAllSkeleton();

        $filtered = FilterHelper::filterByFromDateToDate($filtered, 'created_at', $from_date, $to_date);

        $filtered = FilterHelper::filterByRangeDate($filtered, 'created_at', $range);

        $filtered = FilterHelper::filterByValue($filtered, 'id', $supplier_id);
        $filtered = FilterHelper::filterByValue($filtered, 'city_code', $city_code);
        $filtered = FilterHelper::filterByValue($filtered, 'district_code', $district_code);
        $filtered = FilterHelper::filterByValue($filtered, 'ward_code', $ward_code);

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
        return true;
    }

    public function validateLogic($data)
    {
        $msg_error = [];

        $skip_id = isset($data['id']) ? [$data['id']] : [];

        if ($data['code'] && $this->supplierRepo->existsValue('code', $data['code'], $skip_id))
            array_push($msg_error, 'Mã khách hàng đã tồn tại.');

        if ($this->supplierRepo->existsValue('name', $data['name'], $skip_id))
            array_push($msg_error, 'Tên khách hàng đã tồn tại.');

        if ($data['email'] && !filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            array_push($msg_error, 'Địa chỉ email không hợp lệ.');
        }

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