<?php

namespace App\Services\Implement;

use App\Services\IOCenterServiceInterface;
use App\Repositories\IOCenterRepositoryInterface;
use App\Repositories\DistributorRepositoryInterface;
use App\Repositories\SupplierRepositoryInterface;
use App\Common\DateTimeHelper;
use App\Common\AuthHelper;
use App\Common\FilterHelper;
use DB;
use League\Flysystem\Exception;

class IOCenterService implements IOCenterServiceInterface
{
    private $user;
    private $table_name, $table_names;

    protected $ioCenterRepo, $supplierRepo, $distributorRepo;

    public function __construct(IOCenterRepositoryInterface $ioCenterRepo
        , SupplierRepositoryInterface $supplierRepo
        , DistributorRepositoryInterface $distributorRepo)
    {
        $this->ioCenterRepo    = $ioCenterRepo;
        $this->supplierRepo    = $supplierRepo;
        $this->distributorRepo = $distributorRepo;

        $jwt_data = AuthHelper::getCurrentUser();
        if ($jwt_data['status']) {
            $user_data = AuthHelper::getInfoCurrentUser($jwt_data['user']);
            if ($user_data['status'])
                $this->user = $user_data['user'];
        }

        $this->table_name  = 'io_center';
        $this->table_names = 'io_centers';
    }

    public function readAll()
    {
        $all = $this->ioCenterRepo->findAllSkeleton();

        $distributors = $this->distributorRepo->findAllActive();
        $suppliers    = $this->supplierRepo->findAllActive();

        return [
            $this->table_names => $all,
            'distributors'     => $distributors,
            'suppliers'        => $suppliers,
            'placeholder_code' => $this->ioCenterRepo->generateCode('IOCENTER')
        ];
    }

    public function readOne($id)
    {
        $one = $this->ioCenterRepo->findOneSkeleton($id);

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
                'code'         => $data['code'],
                'name'         => $data['name'],
                'description'  => null,
                'created_date' => date('Y-m-d H:i:s'),
                'updated_date' => null,
                'active'       => true,
                'dis_id'       => $data['dis_id']
            ];

            $one = $this->ioCenterRepo->createOne($i_one);

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

            $one = $this->ioCenterRepo->findOneActive($data['id']);

            $i_one = [
                'code'         => $data['code'],
                'name'         => $data['name'],
                'description'  => null,
                'created_date' => date('Y-m-d H:i:s'),
                'updated_date' => null,
                'active'       => true,
                'dis_id'       => $data['dis_id']
            ];

            $one = $this->ioCenterRepo->updateOne($one, $i_one);

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

            $one = $this->ioCenterRepo->deactivateOne($id);

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

            $one = $this->ioCenterRepo->destroyOne($id);

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
        $io_center_id   = $filter['io_center_id'];
        $supplier_id    = $filter['supplier_id'];
        $distributor_id = $filter['distributor_id'];

        $filtered = $this->ioCenterRepo->findAllSkeleton();

        $filtered = FilterHelper::filterByFromDateToDate($filtered, 'created_at', $from_date, $to_date);

        $filtered = FilterHelper::filterByRangeDate($filtered, 'created_at', $range);

        $filtered = FilterHelper::filterByValue($filtered, 'id', $io_center_id);
        $filtered = FilterHelper::filterByValue($filtered, 'supplier_id', $supplier_id);
        $filtered = FilterHelper::filterByValue($filtered, 'distributor_id', $distributor_id);

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
        if (!$data['code']) return false;
        if (!$data['name']) return false;
        if (!$data['dis_id']) return false;
        return true;
    }

    public function validateLogic($data)
    {
        $msg_error = [];

        $skip_id = isset($data['id']) ? [$data['id']] : [];

        if ($this->ioCenterRepo->existsValue('code', $data['code'], $skip_id))
            array_push($msg_error, 'Mã bộ trung tâm đã tồn tại.');

        if ($this->ioCenterRepo->existsValue('name', $data['name'], $skip_id))
            array_push($msg_error, 'Tên bộ trung tâm đã tồn tại.');

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