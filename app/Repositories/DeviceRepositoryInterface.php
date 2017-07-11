<?php

namespace App\Repositories;

interface DeviceRepositoryInterface
{
    public function findAllSkeleton();

    public function findOneSkeleton($id);

    public function findAllTrayHaveNotProduct($tray_ids);

    public function findAllCardHaveNotUser($card_ids);

    public function findAllByParentIds($parent_ids);

    public function getDeviceByCode($collect_code, $io_center_id, $parent_id, $code, $skip_id = []);
}