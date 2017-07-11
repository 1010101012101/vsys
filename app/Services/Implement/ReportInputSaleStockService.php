<?php

namespace App\Services\Implement;

use App\Services\ReportInputSaleStockServiceInterface;
use App\Repositories\DistributorRepositoryInterface;
use App\Repositories\SupplierRepositoryInterface;
use App\Repositories\UserRepositoryInterface;
use App\Repositories\ProductRepositoryInterface;
use App\Repositories\ProducerRepositoryInterface;
use App\Repositories\ProductTypeRepositoryInterface;
use App\Repositories\DeviceRepositoryInterface;
use App\Repositories\IOCenterRepositoryInterface;
use App\Repositories\HistoryInputOutputRepositoryInterface;
use App\Repositories\UnitRepositoryInterface;
use App\Common\DateTimeHelper;
use App\Common\AuthHelper;
use App\Common\FilterHelper;
use DB;
use League\Flysystem\Exception;

class ReportInputSaleStockService implements ReportInputSaleStockServiceInterface
{
    private $user;
    private $table_name, $table_names;

    protected $distributorRepo, $supplierRepo, $userRepo, $productRepo, $producerRepo
    , $productTypeRepo, $deviceRepo, $ioCenterRepo, $historyInputOutputRepo, $unitRepo;

    public function __construct(DistributorRepositoryInterface $distributorRepo
        , SupplierRepositoryInterface $supplierRepo
        , UserRepositoryInterface $userRepo
        , ProductRepositoryInterface $productRepo
        , ProducerRepositoryInterface $producerRepo
        , ProductTypeRepositoryInterface $productTypeRepo
        , DeviceRepositoryInterface $deviceRepo
        , IOCenterRepositoryInterface $ioCenterRepo
        , HistoryInputOutputRepositoryInterface $historyInputOutputRepo
        , UnitRepositoryInterface $unitRepo)
    {
        $this->distributorRepo = $distributorRepo;
        $this->supplierRepo = $supplierRepo;
        $this->userRepo = $userRepo;
        $this->productRepo = $productRepo;
        $this->producerRepo = $producerRepo;
        $this->productTypeRepo = $productTypeRepo;
        $this->deviceRepo = $deviceRepo;
        $this->ioCenterRepo = $ioCenterRepo;
        $this->historyInputOutputRepo = $historyInputOutputRepo;
        $this->unitRepo = $unitRepo;

        $jwt_data = AuthHelper::getCurrentUser();
        if ($jwt_data['status']) {
            $user_data = AuthHelper::getInfoCurrentUser($jwt_data['user']);
            if ($user_data['status'])
                $this->user = $user_data['user'];
        }

        $this->table_name = 'unit';
        $this->table_names = 'units';
    }

    public function readAll()
    {
        switch ($this->user->dis_or_sup) {
            case 'system':
                $distributors = $this->distributorRepo->findAllActive();
                $distributor  = [];

                $staffs = $this->userRepo->findAllActive();

                $suppliers = $this->supplierRepo->findAllActive();
                $supplier  = null;

                $cabinets = $this->deviceRepo->findAllActiveByFieldName('collect_code', 'Cabinet');
                break;
            case 'sup':
                $suppliers = [];
                $supplier  = $this->supplierRepo->findOneActiveByFieldName('id', $this->user->dis_or_sup_id);

                $distributors = $this->distributorRepo->findAllActiveByFieldName('sup_id', $supplier->id);
                $distributor  = [];

                $distributor_ids        = $distributors->pluck('id')->toArray();
                $staffs_of_distributors = $this->userRepo->findAllActiveByFieldName('dis_or_sup', 'sup')->whereIn('dis_or_sup_id', $distributor_ids);
                $staffs_of_supplier     = $this->userRepo->findAllActiveByFieldName('dis_or_sup', 'sup')->where('dis_or_sup_id', $supplier->id);
                $staffs                 = $staffs_of_distributors;
                foreach ($staffs_of_supplier as $staff)
                    $staffs->push($staff);

                $io_center_ids   = $this->ioCenterRepo->findAllActive()->whereIn('dis_id', $distributor_ids)->pluck('id')->toArray();
                $cabinets        = $this->deviceRepo->findAllActiveByFieldName('collect_code', 'Cabinet')->whereIn('io_center_id', $io_center_ids);
                break;
            case 'dis':
                $distributors = [];
                $distributor  = $this->distributorRepo->findOneActiveByFieldNamewhere('id', $this->user->dis_or_sup_id);

                $suppliers = [];
                $supplier  = $this->supplierRepo->findOneActiveByFieldName('id', $distributor->sup_id);
                array_push($suppliers, $supplier);

                $staffs_of_distributor = $this->userRepo->findOneActiveByFieldName('dis_or_sup', 'dis')->where('dis_or_sup_id', $distributor->id);
                $staffs_of_supplier    = $this->userRepo->findOneActiveByFieldName('dis_or_sup', 'sup')->where('dis_or_sup_id', $supplier->id);
                $staffs                = $staffs_of_distributor;
                foreach ($staffs_of_supplier as $staff)
                    $staffs->push($staff);

                $io_center_ids = $this->ioCenterRepo->findOneActiveByFieldName('dis_id', $distributor->id)->pluck('id')->toArray();
                $cabinets      = $this->deviceRepo->findOneActiveByFieldName('collect_code', 'Cabinet')->whereIn('io_center_id', $io_center_ids);
                break;
            default:
                return null;
                break;
        }

        $products = $this->productRepo->findAllActive();
        $producers = $this->producerRepo->findAllActive();
        $product_types = $this->productTypeRepo->findAllActive();
        $units = $this->unitRepo->findAllActive();

        $response = [
            'suppliers'     => $suppliers,
            'supplier'      => $supplier,
            'distributors'  => $distributors,
            'distributor'   => $distributor,
            'staffs'        => $staffs,
            'products'      => $products,
            'producers'     => $producers,
            'product_types' => $product_types,
            'units'         => $units,
            'cabinets'      => $cabinets
        ];
        return $response;
    }

    public function readStock()
    {
        // TODO: Implement readStock() method.
    }

    public function readStockByUser($report_stocks)
    {
        // TODO: Implement readStockByUser() method.
    }

    public function readStockBySearch($filter)
    {
        // TODO: Implement readStockBySearch() method.
    }

    public function readBySearch($filter, $mode)
    {
        // TODO: Implement readBySearch() method.
    }

    public function readInput()
    {
        // TODO: Implement readInput() method.
    }

    public function readInputByUser($report_inputs)
    {
        // TODO: Implement readInputByUser() method.
    }

    public function readSale()
    {
        // TODO: Implement readSale() method.
    }

    public function readSaleByUser($report_sales)
    {
        // TODO: Implement readSaleByUser() method.
    }

    public function changeColumnName($data, $mode)
    {
        // TODO: Implement changeColumnName() method.
    }

    public function readTotal()
    {
        // TODO: Implement readTotal() method.
    }

    public function readTotalByUser($filter)
    {
        // TODO: Implement readTotalByUser() method.
    }


    /** ===== MY FUNCTION ===== */

}