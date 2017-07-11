<?php

namespace App\Services\Implement;

use App\Services\DistributorServiceInterface;
use App\Repositories\DistributorRepositoryInterface;
use App\Repositories\SupplierRepositoryInterface;
use App\Common\DateTimeHelper;
use App\Common\AuthHelper;
use App\Common\FilterHelper;
use DB;
use League\Flysystem\Exception;

class DistributorService implements DistributorServiceInterface
{
    private $user;
    private $table_name, $table_names;

    protected $distributorRepo, $supplierRepo;

    public function __construct(DistributorRepositoryInterface $distributorRepo
        , SupplierRepositoryInterface $supplierRepo)
    {
        $this->distributorRepo = $distributorRepo;
        $this->supplierRepo    = $supplierRepo;

        $jwt_data = AuthHelper::getCurrentUser();
        if ($jwt_data['status']) {
            $user_data = AuthHelper::getInfoCurrentUser($jwt_data['user']);
            if ($user_data['status'])
                $this->user = $user_data['user'];
        }

        $this->table_name  = 'distributor';
        $this->table_names = 'distributors';
    }

    public function readAll()
    {
        $all       = $this->distributorRepo->findAllSkeleton();
        $suppliers = $this->supplierRepo->findAllActive();

        return [
            $this->table_names => $all,
            'suppliers'        => $suppliers,
            'placeholder_code' => $this->distributorRepo->generateCode('DISTRIBUTOR')
        ];
    }

    public function readOne($id)
    {
        $one = $this->distributorRepo->findOneSkeleton($id);

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
                'code'          => $data['code'] ? $data['code'] : $this->distributorRepo->generateCode('DISTRIBUTOR'),
                'name'          => $data['name'],
                'address'       => $data['address'],
                'ward_code'     => $data['ward_code'],
                'city_code'     => $data['city_code'],
                'district_code' => $data['district_code'],
                'phone'         => $data['phone'],
                'email'         => $data['email'],
                'fax'           => $data['fax'],
                'note'          => $data['note'],
                'active'        => true,
                'sup_id'        => $data['sup_id']
            ];

            $one = $this->distributorRepo->createOne($i_one);

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

            $one = $this->distributorRepo->findOneActive($data['id']);

            $i_one = [
                'code'          => $data['code'] ? $data['code'] : $this->distributorRepo->generateCode('DISTRIBUTOR'),
                'name'          => $data['name'],
                'address'       => $data['address'],
                'ward_code'     => $data['ward_code'],
                'city_code'     => $data['city_code'],
                'district_code' => $data['district_code'],
                'phone'         => $data['phone'],
                'email'         => $data['email'],
                'fax'           => $data['fax'],
                'note'          => $data['note'],
                'active'        => true,
                'sup_id'        => $data['sup_id']
            ];

            $one = $this->distributorRepo->updateOne($one, $i_one);

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

            $one = $this->distributorRepo->deactivateOne($id);

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

            $one = $this->distributorRepo->destroyOne($id);

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
        $from_date      = $filter['from_date'];
        $to_date        = $filter['to_date'];
        $range          = $filter['range'];
        $supplier_id    = $filter['supplier_id'];
        $distributor_id = $filter['distributor_id'];
        $city_code      = $filter['city_code'];
        $district_code  = $filter['district_code'];
        $ward_code      = $filter['ward_code'];

        $filtered = $this->distributorRepo->findAllSkeleton();

        $filtered = FilterHelper::filterByFromDateToDate($filtered, 'created_at', $from_date, $to_date);

        $filtered = FilterHelper::filterByRangeDate($filtered, 'created_at', $range);

        $filtered = FilterHelper::filterByValue($filtered, 'supplier_id', $supplier_id);
        $filtered = FilterHelper::filterByValue($filtered, 'id', $distributor_id);
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
        if (!$data['sup_id']) return false;
        return true;
    }

    public function validateLogic($data)
    {
        $msg_error = [];

        $skip_id = isset($data['id']) ? [$data['id']] : [];

        if ($data['code'] && $this->distributorRepo->existsValue('code', $data['code'], $skip_id))
            array_push($msg_error, 'Mã đại lý đã tồn tại.');

        if ($this->distributorRepo->existsValue('name', $data['name'], $skip_id))
            array_push($msg_error, 'Tên đại lý đã tồn tại.');

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