<?php

namespace App\Services\Implement;

use App\Services\UserCardServiceInterface;
use App\Repositories\UserCardRepositoryInterface;
use App\Repositories\UserCardMoneyRepositoryInterface;
use App\Repositories\UserRepositoryInterface;
use App\Repositories\SupplierRepositoryInterface;
use App\Repositories\DistributorRepositoryInterface;
use App\Repositories\DeviceRepositoryInterface;
use App\Repositories\IOCenterRepositoryInterface;
use App\Repositories\PositionRepositoryInterface;
use App\Common\DateTimeHelper;
use App\Common\AuthHelper;
use App\Common\FilterHelper;
use DB;
use League\Flysystem\Exception;

class UserCardService implements UserCardServiceInterface
{
    private $user;
    private $table_name, $table_names;

    protected $userCardRepo, $userCardMoneyRepo, $userRepo, $supplierRepo
    , $distributorRepo, $deviceRepo, $ioCenterRepo, $positionRepo;

    public function __construct(UserCardRepositoryInterface $userCardRepo
        , UserCardMoneyRepositoryInterface $userCardMoneyRepo
        , UserRepositoryInterface $userRepo
        , SupplierRepositoryInterface $supplierRepo
        , DistributorRepositoryInterface $distributorRepo
        , DeviceRepositoryInterface $deviceRepo
        , IOCenterRepositoryInterface $ioCenterRepo
        , PositionRepositoryInterface $positionRepo)
    {
        $this->userCardRepo      = $userCardRepo;
        $this->userCardMoneyRepo = $userCardMoneyRepo;
        $this->userRepo          = $userRepo;
        $this->supplierRepo      = $supplierRepo;
        $this->distributorRepo   = $distributorRepo;
        $this->deviceRepo        = $deviceRepo;
        $this->ioCenterRepo      = $ioCenterRepo;
        $this->positionRepo      = $positionRepo;

        $jwt_data = AuthHelper::getCurrentUser();
        if ($jwt_data['status']) {
            $user_data = AuthHelper::getInfoCurrentUser($jwt_data['user']);
            if ($user_data['status'])
                $this->user = $user_data['user'];
        }

        $this->table_name  = 'user_card';
        $this->table_names = 'user_cards';
    }

    public function readAll()
    {
        $all = $this->userCardRepo->findAllSkeleton($this->user->dis_or_sup, $this->user->dis_or_sup_id);

        // Nhung nhan vien chua duoc phan the
        $staffs = $this->userRepo->findAllUserHaveNotCard($all->pluck('user_id')->toArray());

        $cards = $this->deviceRepo->findAllCardHaveNotUser($all->pluck('card_id')->toArray());

        // Lay tat ca user de search
        $users        = $this->userRepo->findAllActive();
        $suppliers    = $this->supplierRepo->findAllActive();
        $distributors = $this->distributorRepo->findAllActive();
        $io_centers   = $this->ioCenterRepo->findAllActive();
        $rfids        = $this->deviceRepo->findAllActiveByFieldName('collect_code', 'RFID');
        $positions    = $this->positionRepo->findAllActive();

        return [
            $this->table_names => $all,
            'staffs'           => $staffs,
            'cards'            => $cards,
            'users'            => $users,
            'suppliers'        => $suppliers,
            'distributors'     => $distributors,
            'io_centers'       => $io_centers,
            'rfids'            => $rfids,
            'positions'        => $positions
        ];
    }

    public function readOne($id)
    {
        $one = $this->userCardRepo->findOneSkeleton($id);

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
                // some code
            ];

            $one = $this->userCardRepo->createOne($i_one);

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

            $one = $this->userCardRepo->findOneActive($data['id']);

            $i_one = [
                // some code
            ];

            $one = $this->userCardRepo->updateOne($one, $i_one);

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

            $one = $this->userCardRepo->deactivateOne($id);

            if (!$one) {
                DB::rollback();
                return $result;
            }

            // Deactivate UserCardMoney
            $twos = $this->userCardMoneyRepo->findAllActiveByFieldName('user_card_id', $one->id);
            $twos->each(function ($two, $key) {
                $this->userCardMoneyRepo->deactivateOne($two->id);
            });

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

            $one = $this->userCardRepo->destroyOne($id);

            if (!$one) {
                DB::rollback();
                return $result;
            }

            // Delete UserCardMoney
            $twos = $this->userCardMoneyRepo->findAllActiveByFieldName('user_card_id', $one->id);
            $this->userCardMoneyRepo->destroyAllByIds($twos->pluck('id')->toArray());

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
        $dis_or_sup     = $filter['dis_or_sup'];
        $supplier_id    = $filter['supplier_id'];
        $distributor_id = $filter['distributor_id'];
        $position_id    = $filter['position_id'];
        $io_center_id   = $filter['io_center_id'];
        $rfid_id        = $filter['rfid_id'];
        $fullname       = $filter['fullname'];
        $phone          = $filter['phone'];

        switch ($dis_or_sup) {
            case 'sup':
                $filtered = $this->userCardRepo->findAllSkeleton($dis_or_sup, $supplier_id);
                break;
            case 'dis':
                $filtered = $this->userCardRepo->findAllSkeleton($dis_or_sup, $distributor_id);
                break;
            default:
                $filtered = [];
                break;
        }

        $filtered = FilterHelper::filterByFromDateToDate($filtered, 'created_at', $from_date, $to_date);

        $filtered = FilterHelper::filterByRangeDate($filtered, 'created_at', $range);

        $filtered = FilterHelper::filterByValue($filtered, 'position_id', $position_id);
        $filtered = FilterHelper::filterByValue($filtered, 'io_center_id', $io_center_id);
        $filtered = FilterHelper::filterByValue($filtered, 'parent_id', $rfid_id);
        $filtered = FilterHelper::filterByValue($filtered, 'user_fullname', $fullname);
        $filtered = FilterHelper::filterByValue($filtered, 'user_phone', $phone);

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
        if (!$data['user_id']) return false;
        if (!$data['card_id']) return false;
        return true;
    }

    public function validateLogic($data)
    {
        $msg_error = [];

        $skip_id = isset($data['id']) ? [$data['id']] : [];

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
    public function createOrUpdateOne($data)
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

            $user_id     = $data['user_id'];
            $check_exist = $this->userCardRepo->findOneActiveByFieldName('user_id', $user_id);
            if ($check_exist == null || $user_id == 3) {
                // Insert UserCard
                $i_one = [
                    'user_id'      => $user_id,
                    'card_id'      => $data['card_id'],
                    'total_money'  => 0,
                    'count'        => 0,
                    'created_by'   => $this->user->id,
                    'updated_by'   => 0,
                    'created_date' => date('Y-m-d H:i:s'),
                    'updated_date' => null,
                    'vsys_date'    => date('Y-m-d H:i:s'),
                    'active'       => true
                ];

                $one = $this->userCardRepo->createOne($i_one);

                if (!$one) {
                    DB::rollBack();
                    return $result;
                }

                // Insert UserCardMoney
                $i_two = [
                    'io_center_id' => 0,
                    'device_id'    => 0,
                    'user_card_id' => $one->id,
                    'status'       => 'DPS',
                    'money'        => $one->total_money,
                    'count'        => $one->count,
                    'created_by'   => $this->user->id,
                    'updated_by'   => 0,
                    'created_date' => date('Y-m-d H:i:s'),
                    'updated_date' => null,
                    'vsys_date'    => date('Y-m-d H:i:s'),
                    'active'       => true
                ];

                $two = $this->userCardMoneyRepo->createOne($i_two);
                if (!$two) {
                    DB::rollBack();
                    return $result;
                }
            } else {
                if ($check_exist->total_money > 0) {
                    DB::rollBack();
                    return $result;
                }

                // Update UserCard
                $one = $this->userCardRepo->findOneActive($check_exist->id);

                $i_one = [
                    'card_id'      => $data['card_id'],
                    'updated_by'   => $this->user->id,
                    'updated_date' => date('Y - m - d H:i:s'),
                    'vsys_date'    => date('Y - m - d H:i:s'),
                    'active'       => true
                ];

                $one = $this->userCardRepo->updateOne($one, $i_one);

                if (!$one) {
                    DB::rollBack();
                    return $result;
                }

                // Deactivate UserCardMoney
                $twos = $this->userCardMoneyRepo->findAllActiveByFieldName('user_card_id', $check_exist->id);
                $twos->each(function ($two, $key) {
                    $this->userCardMoneyRepo->deactivateOne($two->id);
                });

                // Insert UserCardMoney
                $i_two = [
                    'io_center_id' => 0,
                    'device_id'    => 0,
                    'user_card_id' => $check_exist->id,
                    'status'       => 'DPS',
                    'money'        => $check_exist->total_money,
                    'count'        => $check_exist->count,
                    'created_by'   => $this->user->id,
                    'updated_by'   => 0,
                    'created_date' => date('Y - m - d H:i:s'),
                    'updated_date' => null,
                    'vsys_date'    => date('Y - m - d H:i:s'),
                    'active'       => true
                ];

                $two = $this->userCardMoneyRepo->createOne($i_two);

                if (!$two) {
                    DB::rollBack();
                    return $result;
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

}