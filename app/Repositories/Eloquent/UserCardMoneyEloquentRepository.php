<?php

namespace App\Repositories\Eloquent;

use App\Repositories\UserCardMoneyRepositoryInterface;
use App\UserCardMoney;

class UserCardMoneyEloquentRepository extends BaseEloquentRepository implements UserCardMoneyRepositoryInterface
{
    /** ===== INIT MODEL ===== */
    public function setModel()
    {
        return UserCardMoney::class;
    }

    /** ===== PUBLIC FUNCTION ===== */
    public function findAllSkeleton()
    {
        return $this->findAllActive();
    }

    public function findOneSkeleton($id)
    {
        return $this->findAllSkeleton()->where('user_card_moneys.id', $id)->first();
    }
}