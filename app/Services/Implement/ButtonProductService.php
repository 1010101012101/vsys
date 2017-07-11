<?php

namespace App\Services\Implement;

use App\Services\ButtonProductServiceInterface;
use App\Repositories\ButtonProductRepositoryInterface;
use App\Repositories\HistoryInputOutputRepositoryInterface;
use App\Repositories\IOCenterRepositoryInterface;
use App\Repositories\DeviceRepositoryInterface;
use App\Repositories\DistributorRepositoryInterface;
use App\Repositories\ProductRepositoryInterface;
use App\Common\DateTimeHelper;
use App\Common\AuthHelper;
use App\Common\FilterHelper;
use DB;
use League\Flysystem\Exception;

class ButtonProductService implements ButtonProductServiceInterface
{
    private $user;
    private $table_name, $table_names;

    protected $buttonProductRepo, $historyInputOutputRepo, $ioCenterRepo
    , $deviceRepo, $distributorRepo, $productRepo;

    public function __construct(ButtonProductRepositoryInterface $buttonProductRepo
        , HistoryInputOutputRepositoryInterface $historyInputOutputRepo
        , IOCenterRepositoryInterface $ioCenterRepo
        , DeviceRepositoryInterface $deviceRepo
        , DistributorRepositoryInterface $distributorRepo
        , ProductRepositoryInterface $productRepo)
    {
        $this->buttonProductRepo      = $buttonProductRepo;
        $this->historyInputOutputRepo = $historyInputOutputRepo;
        $this->ioCenterRepo           = $ioCenterRepo;
        $this->deviceRepo             = $deviceRepo;
        $this->distributorRepo        = $distributorRepo;
        $this->productRepo            = $productRepo;

        $jwt_data = AuthHelper::getCurrentUser();
        if ($jwt_data['status']) {
            $user_data = AuthHelper::getInfoCurrentUser($jwt_data['user']);
            if ($user_data['status'])
                $this->user = $user_data['user'];
        }

        $this->table_name  = 'tray_product';
        $this->table_names = 'tray_products';
    }

    public function readAll()
    {
        $all = $this->buttonProductRepo->findAllSkeleton();

        $products     = $this->productRepo->findAllActive();
        $trays        = $this->deviceRepo->findAllTrayHaveNotProduct($all->pluck('button_id')->toArray());
        $io_centers   = $this->ioCenterRepo->findAllActive();
        $cabinets     = $this->deviceRepo->findAllActiveByFieldName('collect_code', 'Cabinet');
        $distributors = $this->distributorRepo->findAllActive();

        return [
            $this->table_names => $all,
            'products'         => $products,
            'trays'            => $trays,
            'io_centers'       => $io_centers,
            'cabinets'         => $cabinets,
            'distributors'     => $distributors
        ];
    }

    public function readOne($id)
    {
        $one = $this->buttonProductRepo->findOneSkeleton($id);

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

            $one = $this->buttonProductRepo->createOne($i_one);

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

            $one = $this->buttonProductRepo->findOneActive($data['id']);

            $i_one = [
                // some code
            ];

            $one = $this->buttonProductRepo->updateOne($one, $i_one);

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

            $one = $this->buttonProductRepo->deactivateOne($id);

            if (!$one) {
                DB::rollback();
                return $result;
            }

            // Deactivate HistoryInputOutput
            $twos = $this->historyInputOutputRepo->findAllActiveByFieldName('button_product_id', $one->id);
            $twos->each(function ($two, $key) {
                $this->historyInputOutputRepo->deactivateOne($two->id);
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

            $one = $this->buttonProductRepo->destroyOne($id);

            if (!$one) {
                DB::rollback();
                return $result;
            }

            // Delete HistoryInputOutput
            $twos = $this->historyInputOutputRepo->findAllActiveByFieldName('button_product_id', $one->id);
            $this->historyInputOutputRepo->destroyAllByIds($twos->pluck('id')->toArray());

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
        $cabinet_id     = $filter['cabinet_id'];
        $distributor_id = $filter['distributor_id'];
        $product_id     = $filter['product_id'];

        $filtered = $this->buttonProductRepo->findAllSkeleton();

        $filtered = FilterHelper::filterByFromDateToDate($filtered, 'created_date', $from_date, $to_date);

        $filtered = FilterHelper::filterByRangeDate($filtered, 'created_date', $range);

        $filtered = FilterHelper::filterByValue($filtered, 'io_center_id', $io_center_id);
        $filtered = FilterHelper::filterByValue($filtered, 'cabinet_id', $cabinet_id);
        $filtered = FilterHelper::filterByValue($filtered, 'distributor_id', $distributor_id);
        $filtered = FilterHelper::filterByValue($filtered, 'product_id', $product_id);

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
        if (!$data['arr_tray_id']) return false;
        if (!$data['product_id']) return false;
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
    public function createOrUpdateMulti($data)
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
            $arr_tray_id = $data['arr_tray_id'];
            foreach ($arr_tray_id as $tray_id) {
                $check_exist = $this->buttonProductRepo->findOneActiveByFieldName('button_id', $tray_id);
                $tray        = $this->deviceRepo->findOneActive($tray_id);
                $io_center   = $this->ioCenterRepo->findOneActive($tray->io_center_id);

                if ($io_center->dis_id == 0) {
                    DB::rollBack();
                    return $result;
                }
                if ($check_exist == null) {

                    // Insert ButtonProduct
                    $i_one = [
                        'dis_id'        => $io_center->dis_id,
                        'button_id'     => $tray_id,
                        'product_id'    => $data['product_id'],
                        'total_quantum' => 0,
                        'count'         => 0,
                        'created_by'    => $this->user->id,
                        'updated_by'    => 0,
                        'created_date'  => date('Y-m-d H:i:s'),
                        'updated_date'  => null,
                        'vsys_date'     => date('Y-m-d H:i:s'),
                        'active'        => true
                    ];

                    $one = $this->buttonProductRepo->createOne($i_one);

                    if (!$one) {
                        DB::rollBack();
                        return $result;
                    }

                    // Insert HistoryInputOutput
                    $i_two = [
                        'dis_id'            => $one->dis_id,
                        'io_center_id'      => $tray->io_center_id,
                        'button_id'         => $one->button_id,
                        'product_id'        => $one->product_id,
                        'button_product_id' => $one->id,
                        'status'            => 'IN',
                        'quantum_in'        => 0,
                        'quantum_out'       => 0,
                        'count'             => $one->count,
                        'created_by'        => $one->created_by,
                        'updated_by'        => 0,
                        'user_input_id'     => $this->user->id,
                        'user_output_id'    => 0,
                        'created_date'      => date('Y-m-d H:i:s'),
                        'updated_date'      => null,
                        'vsys_date'         => date('Y-m-d H:i:s'),
                        'isDefault'         => true,
                        'adjust_by'         => 0,
                        'active'            => true
                    ];

                    $two = $this->historyInputOutputRepo->createOne($i_two);

                    if (!$two) {
                        DB::rollBack();
                        return $result;
                    }
                } else {
                    if ($check_exist->total_quantum > 0) {
                        DB::rollBack();
                        return $result;
                    }

                    // Update ButtonProduct
                    $one = $this->buttonProductRepo->findOneActive($check_exist->id);

                    $i_one = [
                        'product_id'   => $data['product_id'],
                        'updated_by'   => $this->user->id,
                        'updated_date' => date('Y-m-d H:i:s'),
                        'vsys_date'    => date('Y-m-d H:i:s'),
                        'active'       => true
                    ];

                    $one = $this->buttonProductRepo->updateOne($one, $i_one);

                    if (!$one) {
                        DB::rollBack();
                        return $result;
                    }

                    // Deactivate HistoryInputOutput
                    $twos = $this->historyInputOutputRepo->findAllActiveByFieldName('button_product_id', $check_exist->id);
                    $twos->each(function ($two, $key) {
                        $this->historyInputOutputRepo->deactivateOne($two->id);
                    });

                    // Insert HistoryInputOutput
                    // Insert HistoryInputOutput
                    $i_two = [
                        'dis_id'            => $one->dis_id,
                        'io_center_id'      => $tray->io_center_id,
                        'button_id'         => $one->button_id,
                        'product_id'        => $one->product_id,
                        'button_product_id' => $one->id,
                        'status'            => 'IN',
                        'quantum_in'        => 0,
                        'quantum_out'       => 0,
                        'count'             => $one->count,
                        'created_by'        => $one->created_by,
                        'updated_by'        => 0,
                        'user_input_id'     => $this->user->id,
                        'user_output_id'    => 0,
                        'created_date'      => date('Y-m-d H:i:s'),
                        'updated_date'      => null,
                        'vsys_date'         => date('Y-m-d H:i:s'),
                        'isDefault'         => true,
                        'adjust_by'         => 0,
                        'active'            => true
                    ];

                    $two = $this->historyInputOutputRepo->createOne($i_two);

                    if (!$two) {
                        DB::rollBack();
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

    public function updateTotalQuantumOne($data)
    {
        $result = [
            'status' => false,
            'errors' => []
        ];
        try {
            DB::beginTransaction();
            $one = $this->buttonProductRepo->findOneActive($data['id']);

            if ($one->total_quantum == $data['total_quantum']) {
                DB::rollBack();
                return $result;
            }

            $old_total_quantum = $one->total_quantum;
            $i_one             = [
                'total_quantum' => $data['total_quantum'],
                'active'        => true
            ];

            $one = $this->buttonProductRepo->updateOne($one, $i_one);

            if (!$one) {
                DB::rollBack();
                return $result;
            }

            $status        = ($data['total_quantum'] > $old_total_quantum) ? 'IN' : 'OUT';
            $changeQuantum = abs($data['total_quantum'] - $old_total_quantum);

            $tray = $this->deviceRepo->findOneActive($one->button_id);

            $i_two = [
                'dis_id'            => $one->dis_id,
                'io_center_id'      => $tray->io_center_id,
                'button_id'         => $one->button_id,
                'product_id'        => $one->product_id,
                'button_product_id' => $one->id,
                'status'            => $status
            ];

            switch ($status) {
                case 'IN':
                    $i_two['quantum_in']  = $changeQuantum;
                    $i_two['quantum_out'] = 0;

                    $i_two['user_input_id']  = $this->user->id;
                    $i_two['user_output_id'] = 0;
                    break;
                case 'OUT':
                    $i_two['quantum_in']  = 0;
                    $i_two['quantum_out'] = $changeQuantum;

                    $i_two['user_input_id']  = 0;
                    $i_two['user_output_id'] = $this->user->id;
                    break;
                default:
                    break;
            }

            $i_two['quantum_remain'] = $one->total_quantum;
            $sum                     = $this->historyInputOutputRepo->findAllActiveByFieldName('button_product_id', $one->id);
            $sum_in                  = $sum->where('status', 'IN')->sum('quantum_in') + $i_two['quantum_in'];
            $sum_out                 = $sum->where('status', 'OUT')->sum('quantum_out') + $i_two['quantum_out'];
            $i_two['sum_in']         = $sum_in;
            $i_two['sum_out']        = $sum_out;
            $i_two['product_price']  = 0;
            $i_two['total_pay']      = $changeQuantum * $i_two['product_price'];

            $i_two['count']        = 0;
            $i_two['created_by']   = $this->user->id;
            $i_two['updated_by']   = 0;
            $i_two['created_date'] = date('Y-m-d H:i:s');
            $i_two['updated_date'] = null;
            $i_two['vsys_date']    = date('Y-m-d H:i:s');
            $i_two['isDefault']    = false;
            $i_two['adjust_by']    = $this->user->id;
            $i_two['active']       = true;

            $two = $this->historyInputOutputRepo->createOne($i_two);

            if (!$two) {
                DB::rollBack();
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

}