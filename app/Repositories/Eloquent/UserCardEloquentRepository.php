<?php

namespace App\Repositories\Eloquent;

use App\Repositories\UserCardRepositoryInterface;
use App\UserCard;

class UserCardEloquentRepository extends BaseEloquentRepository implements UserCardRepositoryInterface
{
    /** ===== INIT MODEL ===== */
    public function setModel()
    {
        return UserCard::class;
    }

    /** ===== PUBLIC FUNCTION ===== */
    public function findAllSkeleton($dis_or_sup, $dis_or_sup_id)
    {
        $all = $this->model
            ->where('user_cards.active', true)
            ->where('users.dis_or_sup', $dis_or_sup)
            ->leftJoin('users', 'users.id', '=', 'user_cards.user_id')
            ->leftJoin('positions', 'positions.id', '=', 'users.position_id')
            ->leftJoin('devices', 'devices.id', '=', 'user_cards.card_id')
            ->leftJoin('io_centers', 'io_centers.id', '=', 'devices.io_center_id')
            ->leftJoin('devices as parents', 'parents.id', '=', 'devices.parent_id');

        if($dis_or_sup_id != 0) {
            $all = $all->where('users.dis_or_sup_id', $dis_or_sup_id);
        }

        switch ($dis_or_sup) {
            case 'sup':
                $all = $all
                    ->leftJoin('suppliers', 'suppliers.id', '=', 'users.dis_or_sup_id')
                    ->select('user_cards.*', 'positions.name as position_name', 'users.fullname as user_fullname', 'users.phone as user_phone'
                        , 'devices.code as card_code', 'devices.name as card_name', 'devices.description as card_description'
                        , 'io_centers.code as io_center_code', 'io_centers.name as io_center_name', 'io_centers.description as io_center_description'
                        , 'parents.id as parent_id', 'parents.code as parent_code', 'parents.name as parent_name', 'parents.description as parent_description'
                        , 'suppliers.name as supplier_name'
                    );
                break;
            case 'dis':
                $all = $all
                    ->leftJoin('distributors', 'distributors.id', '=', 'users.dis_or_sup_id')
                    ->select('user_cards.*', 'positions.name as position_name', 'users.fullname as user_fullname', 'users.phone as user_phone'
                        , 'devices.code as card_code', 'devices.name as card_name', 'devices.description as card_description'
                        , 'io_centers.code as io_center_code', 'io_centers.name as io_center_name', 'io_centers.description as io_center_description'
                        , 'parents.id as parent_id', 'parents.code as parent_code', 'parents.name as parent_name', 'parents.description as parent_description'
                        , 'distributors.name as distributor_name'
                    );
                break;
            default:
                break;
        }
        return $all->get();
    }

    public function findOneSkeleton($id)
    {
        $one = $this->findOneActive($id);
        return $this->findAllSkeleton($one->dis_or_sup, $one->dis_or_sup_id)->where('user_cards.id', $id)->first();
    }
}