<?php

namespace App\Services\Implement;

use App\Services\DeviceServiceInterface;
use App\Repositories\DeviceRepositoryInterface;
use App\Repositories\CollectionRepositoryInterface;
use App\Repositories\IOCenterRepositoryInterface;
use App\Common\DateTimeHelper;
use App\Common\AuthHelper;
use App\Common\FilterHelper;
use DB;
use League\Flysystem\Exception;

class DeviceService implements DeviceServiceInterface
{
    private $user;
    private $table_name, $table_names;

    protected $deviceRepo, $collectionRepo, $ioCenterRepo;

    public function __construct(DeviceRepositoryInterface $deviceRepo
        , CollectionRepositoryInterface $collectionRepo
        , IOCenterRepositoryInterface $ioCenterRepo)
    {
        $this->deviceRepo     = $deviceRepo;
        $this->collectionRepo = $collectionRepo;
        $this->ioCenterRepo   = $ioCenterRepo;

        $jwt_data = AuthHelper::getCurrentUser();
        if ($jwt_data['status']) {
            $user_data = AuthHelper::getInfoCurrentUser($jwt_data['user']);
            if ($user_data['status'])
                $this->user = $user_data['user'];
        }

        $this->table_name  = 'device';
        $this->table_names = 'devices';
    }

    public function readAll()
    {
        $all = $this->deviceRepo->findAllSkeleton();

        $collections = $this->collectionRepo->findAllActive();
        $io_centers  = $this->ioCenterRepo->findAllActive();

        return [
            $this->table_names => $all,
            'collections'      => $collections,
            'io_centers'       => $io_centers,
            'placeholder_code' => $this->deviceRepo->generateCode('DEVICE')
        ];
    }

    public function readOne($id)
    {
        $one = $this->deviceRepo->findOneSkeleton($id);

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

            $collection = $this->collectionRepo->findOneActiveByFieldName('code', $data['collect_code']);

            $i_one = [
                'collect_code'    => $data['collect_code'],
                'code'            => $data['code'] ? $data['code'] : $this->deviceRepo->generateCode(strtoupper($data['collect_code'])),
                'name'            => $data['name'],
                'description'     => null,
                'quantum_product' => $data['collect_code'] == 'Tray' ? $data['quantum_product'] : 0,
                'active'          => true,
                'collect_id'      => $collection->id,
                'io_center_id'    => $data['io_center_id'],
                'parent_id'       => $data['parent_id']
            ];

            $one = $this->deviceRepo->createOne($i_one);

            if (!$one) {
                DB::rollback();
                return $result;
            }

            if ($data['quantum_tray'] > 0 && $data['collect_code'] == 'Cabinet') {
                $collection = $this->collectionRepo->findOneActiveByFieldName('code', 'Tray');
                for ($i = 1; $i <= $data['quantum_tray']; $i++) {
                    $i_two = [
                        'collect_code'    => 'Tray',
                        'code'            => $i,
                        'name'            => 'Box ' . $i,
                        'description'     => null,
                        'quantum_product' => $data['quantum_product'],
                        'active'          => true,
                        'collect_id'      => $collection->id,
                        'io_center_id'    => $data['io_center_id'],
                        'parent_id'       => $one->id
                    ];

                    $two = $this->deviceRepo->createOne($i_two);
                    if (!$two) {
                        DB::rollback();
                        return $result;
                    }
                }
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

            $one = $this->deviceRepo->findOneActive($data['id']);

            $collection = $this->collectionRepo->findOneActiveByFieldName('code', $data['collect_code']);

            $i_one = [
                'collect_code'    => $data['collect_code'],
                'code'            => $data['code'] ? $data['code'] : $this->deviceRepo->generateCode(strtoupper($data['collect_code'])),
                'name'            => $data['name'],
                'description'     => null,
                'quantum_product' => $data['quantum_product'],
                'active'          => true,
                'collect_id'      => $collection->id,
                'io_center_id'    => $data['io_center_id'],
                'parent_id'       => $data['parent_id']
            ];

            $one = $this->deviceRepo->updateOne($one, $i_one);

            if (!$one) {
                DB::rollback();
                return $result;
            }

            if ($data['quantum_tray'] > 0 && $data['collect_code'] == 'Cabinet') {
                $collection = $this->collectionRepo->findOneActiveByFieldName('code', 'Tray');
                for ($i = 1; $i <= $data['quantum_tray']; $i++) {
                    $i_two = [
                        'collect_code'    => 'Tray',
                        'code'            => $i,
                        'name'            => 'Box ' . $i,
                        'description'     => null,
                        'quantum_product' => $data['quantum_product'],
                        'active'          => true,
                        'collect_id'      => $collection->id,
                        'io_center_id'    => $data['io_center_id'],
                        'parent_id'       => $one->id
                    ];

                    $two = $this->deviceRepo->createOne($i_two);
                    if (!$two) {
                        DB::rollback();
                        return $result;
                    }
                }
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

            $one = $this->deviceRepo->deactivateOne($id);

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

            $one = $this->deviceRepo->destroyOne($id);

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
        $from_date    = $filter['from_date'];
        $to_date      = $filter['to_date'];
        $range        = $filter['range'];
        $io_center_id = $filter['io_center_id'];
        $device_id    = $filter['device_id'];
        $collect_code = $filter['collect_code'];
        $parent_id    = $filter['parent_id'];

        $filtered = $this->deviceRepo->findAllSkeleton();

        $filtered = FilterHelper::filterByFromDateToDate($filtered, 'created_at', $from_date, $to_date);

        $filtered = FilterHelper::filterByRangeDate($filtered, 'created_at', $range);

        $filtered = FilterHelper::filterByValue($filtered, 'io_center_id', $io_center_id);
        $filtered = FilterHelper::filterByValue($filtered, 'id', $device_id);
        $filtered = FilterHelper::filterByValue($filtered, 'collect_code', $collect_code);
        $filtered = FilterHelper::filterByValue($filtered, 'parent_id', $parent_id);

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
        if (!$data['collect_code']) return false;
        if (!$data['io_center_id']) return false;
        if (!is_numeric($data['quantum_product'])) return false;
        if (!is_numeric($data['quantum_tray'])) return false;
        return true;
    }

    public function validateLogic($data)
    {
        $msg_error = [];

        $skip_id = isset($data['id']) ? [$data['id']] : [];

        if ($data['collect_code'] == 'Tray') {
            // Check exist Tray by code along with Cabinet
            $tray = $this->deviceRepo->getDeviceByCode('Tray', null, $data['parent_id'], $data['code'], $skip_id);
            if ($tray)
                array_push($msg_error, 'Mã box đã tồn tại trong tủ này.');
        } else {
            if ($data['code'] && $this->deviceRepo->existsValue('code', $data['code'], $skip_id))
                array_push($msg_error, 'Mã thiết bị đã tồn tại.');
        }

//        if ($this->checkExistData(Device::class, 'name', $data['name'], $skip_id))
//            array_push($msg_error, 'Tên thiết bị đã tồn tại.');

        if ($data['quantum_tray'] > 0 && $data['collect_code'] == 'Cabinet') {
            $childs = $this->deviceRepo->findAllByParentIds($skip_id)->count();
            if($childs > 0) {
                array_push($msg_error, 'Chỉ được nhập nhanh box khi tủ không có box nào.');
            }
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