<?php

namespace App\Services\Implement;

use App\Services\UserServiceInterface;
use App\Repositories\UserRepositoryInterface;
use App\Repositories\PositionRepositoryInterface;
use App\Repositories\DistributorRepositoryInterface;
use App\Repositories\SupplierRepositoryInterface;
use App\Repositories\FileRepositoryInterface;
use App\Repositories\UserRoleRepositoryInterface;
use App\Common\DateTimeHelper;
use App\Common\AuthHelper;
use App\Common\FilterHelper;
use DB;
use League\Flysystem\Exception;
use Hash;

class UserService implements UserServiceInterface
{
    private $user;
    private $table_name, $table_names;
    private $auth_unam_pwd, $fake_pwd;

    protected $userRepo, $positionRepo, $distributorRepo
    , $supplierRepo, $fileRepo, $userRoleRepo;

    public function __construct(UserRepositoryInterface $userRepo
        , PositionRepositoryInterface $positionRepo
        , DistributorRepositoryInterface $distributorRepo
        , SupplierRepositoryInterface $supplierRepo
        , FileRepositoryInterface $fileRepo
        , UserRoleRepositoryInterface $userRoleRepo)
    {
        $this->userRepo        = $userRepo;
        $this->positionRepo    = $positionRepo;
        $this->distributorRepo = $distributorRepo;
        $this->supplierRepo    = $supplierRepo;
        $this->fileRepo        = $fileRepo;
        $this->userRoleRepo    = $userRoleRepo;

        $jwt_data = AuthHelper::getCurrentUser();
        if ($jwt_data['status']) {
            $user_data = AuthHelper::getInfoCurrentUser($jwt_data['user']);
            if ($user_data['status'])
                $this->user = $user_data['user'];
        }

        $this->table_name  = 'user';
        $this->table_names = 'users';

        $this->auth_unam_pwd = ($this->user->position_id == 1) ? true : false;
        $this->fake_pwd      = substr(config('app.key'), 10);
    }

    public function readAll()
    {
        $all = $this->userRepo->findAllSkeleton($this->user->dis_or_sup, $this->user->dis_or_sup_id);

        $positions    = $this->positionRepo->findAllActive();
        $distributors = $this->distributorRepo->findAllActive();
        $suppliers    = $this->supplierRepo->findAllActive();

        return [
            $this->table_names => $all,
            'distributors'     => $distributors,
            'suppliers'        => $suppliers,
            'positions'        => $positions,
            'auth'             => $this->auth_unam_pwd,
            'fake_pwd'         => $this->fake_pwd,
            'placeholder_code' => $this->userRepo->generateCode('USER')
        ];
    }

    public function readOne($id)
    {
        $one = $this->userRepo->findOneSkeleton($id);

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
                'code'          => $data['code'] ? $data['code'] : $this->userRepo->generateCode('USER'),
                'fullname'      => $data['fullname'],
                'username'      => null,
                'password'      => null,
                'address'       => $data['address'],
                'phone'         => $data['phone'],
                'birthday'      => date('Y-m-d'),
                'sex'           => $data['sex'],
                'email'         => $data['email'],
                'note'          => $data['note'],
                'created_by'    => $this->user->id,
                'updated_by'    => 0,
                'created_date'  => date('Y-m-d H:i:s'),
                'updated_date'  => null,
                'active'        => true,
                'position_id'   => $data['position_id'],
                'dis_or_sup'    => $data['dis_or_sup'],
                'dis_or_sup_id' => $data['dis_or_sup_id']
            ];

            if ($this->auth_unam_pwd) {
                $i_one['username'] = $data['username'];
                $i_one['password'] = Hash::make($data['password']);
            }

            $one = $this->userRepo->createOne($i_one);

            if (!$one) {
                DB::rollback();
                return $result;
            }

            # Insert UserRole

            # Find Array Role
            $roles = [1];
            switch ($one->dis_or_sup) {
                case 'sup':
                    switch ($one->position_id) {
                        case 3:
                            array_push($roles, 15); //ReportSupplier
                            array_push($roles, 12); //ButtonProduct
                            array_push($roles, 6); //Product
                            break;
                        case 4:
                        case 5:
                            array_push($roles, 17); //ReportStaffInput
                            break;
                        default:
                            break;
                    }
                    break;
                case 'dis':
                    switch ($one->position_id) {
                        case 3:
                            array_push($roles, 16); //ReportDistributor
                            array_push($roles, 12);
                            array_push($roles, 6);
                            break;
                        case 4:
                        case 5:
                            array_push($roles, 16);
                            break;
                        default:
                            break;
                    }
                    break;
                default:
                    break;
            }

            # Add Role
            foreach ($roles as $role_id) {
                $i_two = [
                    'user_id'      => $one->id,
                    'role_id'      => $role_id,
                    'created_by'   => $this->user->id,
                    'updated_by'   => 0,
                    'created_date' => date('Y-m-d H:i:s'),
                    'updated_date' => null,
                    'active'       => true
                ];

                $two = $this->userRoleRepo->createOne($i_two);

                if (!$two) {
                    DB::rollback();
                    return $result;
                }
            }

            # Insert File
            $i_three = [
                'code'         => $this->fileRepo->generateCode('FILE'),
                'name'         => $one->fullname,
                'extension'    => 'png',
                'mime_type'    => 'image/png',
                'path'         => 'assets/img/a' . 'default' . '.png',
                'size'         => 0,
                'table_name'   => 'users',
                'table_id'     => $one->id,
                'note'         => '',
                'created_date' => date('Y-m-d H:i:s'),
                'updated_date' => null,
                'active'       => true
            ];

            $three = $this->fileRepo->createOne($i_three);

            if (!$three) {
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

            $one = $this->userRepo->findOneActive($data['id']);

            $i_one = [
                'code'          => $data['code'],
                'fullname'      => $data['fullname'],
                'address'       => $data['address'],
                'phone'         => $data['phone'],
                'birthday'      => date('Y-m-d'),
                'sex'           => $data['sex'],
                'email'         => $data['email'],
                'note'          => $data['note'],
                'updated_by'    => $this->user->id,
                'updated_date'  => date('Y-m-d H:i:s'),
                'active'        => true,
                'position_id'   => $data['position_id'],
                'dis_or_sup'    => $data['dis_or_sup'],
                'dis_or_sup_id' => $data['dis_or_sup_id']
            ];

            if ($this->auth_unam_pwd) {
                $i_one['username'] = $data['username'];

                if ($data['password'] != $this->fake_pwd)
                    $i_one['password'] = Hash::make($data['password']);
            }

            $one = $this->userRepo->updateOne($one, $i_one);

            if (!$one) {
                DB::rollback();
                return $result;
            }

            # Delete UserRole
            $twos = $this->userRoleRepo->findAllActiveByFieldName('user_id', $one->id);
            $this->userRoleRepo->destroyAllByIds($twos->pluck('id')->toArray());

            # Insert UserRole

            //
            # Find Array Role
            $roles = [1];
            switch ($one->dis_or_sup) {
                case 'sup':
                    switch ($one->position_id) {
                        case 3:
                            array_push($roles, 15); //ReportSupplier
                            array_push($roles, 12); //ButtonProduct
                            array_push($roles, 6); //Product
                            break;
                        case 4:
                        case 5:
                            array_push($roles, 17); //ReportStaffInput
                            break;
                        default:
                            break;
                    }
                    break;
                case 'dis':
                    switch ($one->position_id) {
                        case 3:
                            array_push($roles, 16); //ReportDistributor
                            array_push($roles, 12);
                            array_push($roles, 6);
                            break;
                        case 4:
                        case 5:
                            array_push($roles, 16);
                            break;
                        default:
                            break;
                    }
                    break;
                default:
                    break;
            }

            # Add Role
            foreach ($roles as $role_id) {
                $i_two = [
                    'user_id'      => $one->id,
                    'role_id'      => $role_id,
                    'created_by'   => $this->user->id,
                    'updated_by'   => 0,
                    'created_date' => date('Y-m-d H:i:s'),
                    'updated_date' => null,
                    'active'       => true
                ];

                $two = $this->userRoleRepo->createOne($i_two);

                if (!$two) {
                    DB::rollback();
                    return $result;
                }
            }
            //

            # File

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

            $one = $this->userRepo->deactivateOne($id);

            if (!$one) {
                DB::rollback();
                return $result;
            }

            # UserRole
            $twos = $this->userRoleRepo->findAllActiveByFieldName('user_id', $one->id);
            $twos->each(function ($two, $key) {
                $this->userRoleRepo->deactivateOne($two->id);
            });

            # File
            $three = $this->fileRepo->findOneActiveByTableNameAndTableId('users', $one->id);
            $this->fileRepo->deactivateOne($three->id);

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

            $one = $this->userRepo->destroyOne($id);

            if (!$one) {
                DB::rollback();
                return $result;
            }

            # UserRole
            $twos = $this->userRoleRepo->findAllActiveByFieldName('user_id', $one->id);
            $this->userRoleRepo->destroyAllByIds($twos->pluck('id')->toArray());

            # File
            $three = $this->fileRepo->findOneActiveByTableNameAndTableId('users', $one->id);
            $this->fileRepo->destroyOne($three->id);

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
        $code           = $filter['code'];
        $fullname       = $filter['fullname'];
        $username       = $filter['username'];
        $phone          = $filter['phone'];

        switch ($dis_or_sup) {
            case 'sup':
                $filtered = $this->userRepo->findAllSkeleton($dis_or_sup, $supplier_id);
                break;
            case 'dis':
                $filtered = $this->userRepo->findAllSkeleton($dis_or_sup, $distributor_id);
                break;
            default:
                $filtered = [];
                break;
        }

        $filtered = FilterHelper::filterByFromDateToDate($filtered, 'created_at', $from_date, $to_date);

        $filtered = FilterHelper::filterByRangeDate($filtered, 'created_at', $range);

        $filtered = FilterHelper::filterByValue($filtered, 'position_id', $position_id);
        $filtered = FilterHelper::filterByValue($filtered, 'code', $code);
        $filtered = FilterHelper::filterByValue($filtered, 'fullname', $fullname);
        $filtered = FilterHelper::filterByValue($filtered, 'username', $username);
        $filtered = FilterHelper::filterByValue($filtered, 'phone', $phone);

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
        if (!$data['fullname']) return false;
        if (!$data['position_id']) return false;
        if (!$data['dis_or_sup']) return false;
        if (!$data['dis_or_sup_id']) return false;
        return true;
    }

    public function validateLogic($data)
    {
        $msg_error = [];

        $skip_id = isset($data['id']) ? [$data['id']] : [];

        if ($data['code'] && $this->userRepo->existsValue('code', $data['code'], $skip_id))
            array_push($msg_error, 'Mã người dùng đã tồn tại.');

        if ($this->userRepo->existsValue('fullname', $data['fullname'], $skip_id))
            array_push($msg_error, 'Tên người dùng đã tồn tại.');

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
    public function changePassword($data)
    {
        if ($data['password'] == $data['new_password'])
            return ['error' => 'Mật khẩu cũ và mới không được trùng nhau.', 'status_code' => 404];
        $user = $this->userRepo->findOneActive($this->user->id);

        if (!$user) {
            return ['error' => 'Người dùng không tồn tại.', 'error_en' => 'user is not exist', 'status_code' => 401];
        }
        $password_check = Hash::check($data['password'], $user->password);
        if (!$password_check) {
            return ['error' => 'Mật khẩu không hợp lệ.', 'error_en' => 'password is not correct', 'status_code' => 401];
        }

        $i_user = [
            'password' => Hash::make($data['new_password'])
        ];

        $user = $this->userRepo->updateOne($user, $i_user);
        if (!$user)
            return ['error' => 'Kết nối đến máy chủ thất bại, vui lòng làm mới trình duyệt và thử lại.', 'status_code' => 404];
        return ['status_code' => 200];
    }
}