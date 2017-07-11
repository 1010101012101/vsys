<?php

namespace App\Repositories\Eloquent;

use App\Repositories\GroupRoleRepositoryInterface;
use App\GroupRole;

class GroupRoleEloquentRepository extends BaseEloquentRepository implements GroupRoleRepositoryInterface
{
    /** ===== INIT MODEL ===== */
    public function setModel()
    {
        return GroupRole::class;
    }

    /** ===== PUBLIC FUNCTION ===== */
    public function findAllSkeleton()
    {
        return $this->findAllActive();
    }

    public function findOneSkeleton($id)
    {
        return $this->findAllSkeleton()->where('group_roles.id', $id)->first();
    }
}