<?php

namespace App\Repositories\Eloquent;

use App\Repositories\RoleRepositoryInterface;
use App\Role;

class RoleEloquentRepository extends BaseEloquentRepository implements RoleRepositoryInterface
{
    /** ===== INIT MODEL ===== */
    public function setModel()
    {
        return Role::class;
    }

    /** ===== PUBLIC FUNCTION ===== */
    public function findAllSkeleton()
    {
        return $this->findAllActive();
    }

    public function findOneSkeleton($id)
    {
        return $this->findAllSkeleton()->where('roles.id', $id)->first();
    }
}