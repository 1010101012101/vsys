<?php

namespace App\Services\Implement;

use App\Services\ProducerServiceInterface;
use App\Repositories\ProducerRepositoryInterface;
use App\Common\DateTimeHelper;
use App\Common\AuthHelper;
use App\Common\FilterHelper;
use DB;
use League\Flysystem\Exception;

class ProducerService implements ProducerServiceInterface
{
    private $user;
    private $table_name, $table_names;

    protected $producerRepo;

    public function __construct(ProducerRepositoryInterface $producerRepo)
    {
        $this->producerRepo = $producerRepo;

        $jwt_data = AuthHelper::getCurrentUser();
        if ($jwt_data['status']) {
            $user_data = AuthHelper::getInfoCurrentUser($jwt_data['user']);
            if ($user_data['status'])
                $this->user = $user_data['user'];
        }

        $this->table_name  = 'producer';
        $this->table_names = 'producers';
    }

    public function readAll()
    {
        $all = $this->producerRepo->findAllSkeleton();

        return [
            $this->table_names => $all,
            'placeholder_code' => $this->producerRepo->generateCode('PRODUCER')
        ];
    }

    public function readOne($id)
    {
        $one = $this->producerRepo->findOneSkeleton($id);

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
                'code'    => $data['code'] ? $data['code'] : $this->producerRepo->generateCode('PRODUCER'),
                'name'    => $data['name'],
                'address' => $data['address'],
                'phone'   => $data['phone'],
                'email'   => $data['email'],
                'fax'     => $data['fax'],
                'note'    => $data['note'],
                'active'  => true
            ];

            $one = $this->producerRepo->createOne($i_one);

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

            $one = $this->producerRepo->findOneActive($data['id']);

            $i_one = [
                'code'    => $data['code'] ? $data['code'] : $this->producerRepo->generateCode('PRODUCER'),
                'name'    => $data['name'],
                'address' => $data['address'],
                'phone'   => $data['phone'],
                'email'   => $data['email'],
                'fax'     => $data['fax'],
                'note'    => $data['note'],
                'active'  => true
            ];

            $one = $this->producerRepo->updateOne($one, $i_one);

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

            $one = $this->producerRepo->deactivateOne($id);

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

            $one = $this->producerRepo->destroyOne($id);

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
        $from_date   = $filter['from_date'];
        $to_date     = $filter['to_date'];
        $range       = $filter['range'];
        $producer_id = $filter['producer_id'];

        $filtered = $this->producerRepo->findAllSkeleton();

        $filtered = FilterHelper::filterByFromDateToDate($filtered, 'created_at', $from_date, $to_date);

        $filtered = FilterHelper::filterByRangeDate($filtered, 'created_at', $range);

        $filtered = FilterHelper::filterByValue($filtered, 'id', $producer_id);

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

        if ($data['code'] && $this->producerRepo->existsValue('code', $data['code'], $skip_id))
            array_push($msg_error, 'Mã nhà cung cấp sản phẩm đã tồn tại.');

        if ($this->producerRepo->existsValue('name', $data['name'], $skip_id))
            array_push($msg_error, 'Tên nhà cung cấp sản phẩm đã tồn tại.');

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