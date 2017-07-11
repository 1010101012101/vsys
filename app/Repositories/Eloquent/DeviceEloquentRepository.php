<?php

namespace App\Repositories\Eloquent;

use App\Repositories\DeviceRepositoryInterface;
use App\Device;

class DeviceEloquentRepository extends BaseEloquentRepository implements DeviceRepositoryInterface
{
    /** ===== INIT MODEL ===== */
    public function setModel()
    {
        return Device::class;
    }

    /** ===== PUBLIC FUNCTION ===== */
    public function findAllSkeleton()
    {
        return $this->model
            ->where('devices.active', true)
            ->leftJoin('collections', 'collections.id', '=', 'devices.collect_id')
            ->leftJoin('io_centers', 'io_centers.id', '=', 'devices.io_center_id')
            ->leftJoin('devices as parents', 'parents.id', '=', 'devices.parent_id')
            ->select('devices.*', 'parents.name as parent_name'
                , 'collections.code as collect_code', 'collections.name as collect_name'
                , 'io_centers.code as io_center_code', 'io_centers.name as io_center_name'
            )
            ->get();
    }

    public function findOneSkeleton($id)
    {
        return $this->findAllSkeleton()->where('devices.id', $id)->first();
    }

    public function findAllTrayHaveNotProduct($tray_ids)
    {
        return $this->model
            ->where('devices.active', true)
            ->where('devices.collect_code', 'Tray')
            ->whereNotIn('devices.id', $tray_ids)
            ->leftJoin('devices as cabinets', 'cabinets.id', '=', 'devices.parent_id')
            ->select('devices.*'
                , 'cabinets.code as cabinet_code', 'cabinets.name as cabinet_name'
            )
            ->get();
    }

    public function findAllCardHaveNotUser($card_ids)
    {
        return $this->model
            ->whereActive(true)
            ->where('collect_code', 'Card')
            ->whereNotIn('id', $card_ids)
            ->get();
    }

    public function findAllByParentIds($parent_ids)
    {
        return $this->model
            ->whereActive(true)
            ->whereIn('parent_id', $parent_ids)
            ->get();
    }

    public function getDeviceByCode($collect_code, $io_center_id, $parent_id, $code, $skip_id = [])
    {
        $devices = $this->model->whereActive(true)
            ->where('collect_code', $collect_code)
            ->where('io_center_id', $io_center_id)
            ->where('parent_id', $parent_id)
            ->where('code', $code)
            ->whereNotIn('id', $skip_id)
            ->get();

        $device = null;
        switch ($devices->count()) {
            case 0:
                break;
            case 1:
                $device = $devices->first();
                break;
            default:
                // $this->createLogging('Cảnh báo', "Trùng mã thiết bị {$code}.", 0, '', "TinTan", "warning");
                break;
        }
        return $device;
    }
}