<?php

namespace App\Repositories\Eloquent;

use App\Repositories\UserRoleRepositoryInterface;
use App\UserRole;

class UserRoleEloquentRepository extends BaseEloquentRepository implements UserRoleRepositoryInterface
{
    /** ===== INIT MODEL ===== */
    public function setModel()
    {
        return UserRole::class;
    }

    /** ===== PUBLIC FUNCTION ===== */
    public function findAllSkeleton()
    {
        return $this->findAllActive();
    }

    public function findOneSkeleton($id)
    {
        return $this->findAllSkeleton()->where('user_roles.id', $id)->first();
    }
}