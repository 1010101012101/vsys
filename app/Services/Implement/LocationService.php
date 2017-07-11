<?php

namespace App\Services\Implement;

use App\Services\LocationServiceInterface;
use App\Repositories\CityRepositoryInterface;
use App\Repositories\DistrictRepositoryInterface;
use App\Repositories\WardRepositoryInterface;
use App\Common\DateTimeHelper;
use App\Common\AuthHelper;
use DB;
use League\Flysystem\Exception;

class LocationService implements LocationServiceInterface
{
    private $user;
    private $table_name, $table_names;

    protected $cityRepo, $districtRepo, $wardRepo;

    public function __construct(CityRepositoryInterface $cityRepo
        , DistrictRepositoryInterface $districtRepo
        , WardRepositoryInterface $wardRepo)
    {
        $this->cityRepo     = $cityRepo;
        $this->districtRepo = $districtRepo;
        $this->wardRepo     = $wardRepo;

        $jwt_data = AuthHelper::getCurrentUser();
        if ($jwt_data['status']) {
            $user_data = AuthHelper::getInfoCurrentUser($jwt_data['user']);
            if ($user_data['status'])
                $this->user = $user_data['user'];
        }

        $this->table_name  = '';
        $this->table_names = '';
    }

    public function readAll()
    {
        $cities    = $this->cityRepo->findAllActive();
        $districts = $this->districtRepo->findAllActive();
        $wards     = $this->wardRepo->findAllActive();

        return [
            'cities'    => $cities,
            'districts' => $districts,
            'wards'     => $wards
        ];
    }

    public function readOne($id)
    {
        // TODO: Implement readOne() method.
    }

    public function createOne($data)
    {
        // TODO: Implement createOne() method.
    }

    public function updateOne($data)
    {
        // TODO: Implement updateOne() method.
    }

    public function deactivateOne($id)
    {
        // TODO: Implement deactivateOne() method.
    }

    public function deleteOne($id)
    {
        // TODO: Implement deleteOne() method.
    }

    public function searchOne($filter)
    {
        // TODO: Implement searchOne() method.
    }

    public function validateInput($data)
    {
        // TODO: Implement validateInput() method.
    }

    public function validateEmpty($data)
    {
        // TODO: Implement validateEmpty() method.
    }

    public function validateLogic($data)
    {
        // TODO: Implement validateLogic() method.
    }

    public function validateUpdateOne($id)
    {
        // TODO: Implement validateUpdateOne() method.
    }

    public function validateDeactivateOne($id)
    {
        // TODO: Implement validateDeactivateOne() method.
    }

    public function validateDeleteOne($id)
    {
        // TODO: Implement validateDeleteOne() method.
    }


    /** ===== MY FUNCTION ===== */

}