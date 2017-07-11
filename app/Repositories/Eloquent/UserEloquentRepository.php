<?php

namespace App\Repositories\Eloquent;

use App\Repositories\UserRepositoryInterface;
use App\User;
use App\Common\DBHelper;
use DB;

class UserEloquentRepository extends BaseEloquentRepository implements UserRepositoryInterface
{
    /** ===== INIT MODEL ===== */
    public function setModel()
    {
        return User::class;
    }

    /** ===== PUBLIC FUNCTION ===== */
    public function findAllSkeleton($dis_or_sup, $dis_or_sup_id)
    {
        $all = $this->model
            ->where('users.active', true)
            ->where('users.dis_or_sup', $dis_or_sup)
            ->leftJoin('positions', 'positions.id', '=', 'users.position_id')
            ->leftJoin('user_cards', 'user_cards.id', '=', 'users.id');

        if($dis_or_sup_id != 0) {
            $all = $all->where('users.dis_or_sup_id', $dis_or_sup_id);
        }

        switch ($dis_or_sup) {
            case 'sup':
                $all = $all
                    ->leftJoin('suppliers', 'suppliers.id', '=', 'users.dis_or_sup_id')
                    ->select('users.*'
                        , 'user_cards.total_money'
                        , DB::raw(DBHelper::getWithCurrencyFormat('user_cards.total_money', 'fc_total_money'))
                        , 'positions.name as position_name'
                        , 'suppliers.id as supplier_id', 'suppliers.name as supplier_name'
                        , DB::raw(DBHelper::getWithDateTimeFormat('users.birthday', 'fd_birthday'))
                    );
                break;
            case 'dis':
                $all = $all
                    ->leftJoin('distributors', 'distributors.id', '=', 'users.dis_or_sup_id')
                    ->select('users.*'
                        , 'user_cards.total_money'
                        , DB::raw(DBHelper::getWithCurrencyFormat('user_cards.total_money', 'fc_total_money'))
                        , 'positions.name as position_name'
                        , 'distributors.id as distributor_id', 'distributors.name as distributor_name'
                        , DB::raw(DBHelper::getWithDateTimeFormat('users.birthday', 'fd_birthday'))
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
        return $this->findAllSkeleton($one->dis_or_sup, $one->dis_or_sup_id)->where('users.id', $id)->first();
    }

    public function findAllUserHaveNotCard($user_ids)
    {
        return $this->model
            ->where('users.active', true)
            ->whereNotIn('position_id', [1, 2])
            ->whereNotIn('id', $user_ids)
            ->leftJoin('positions', 'positions.id', '=', 'users.position_id')
            ->select('users.*', 'positions.name as position_name')
            ->get();
    }
}