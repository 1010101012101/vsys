<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Services\UserCardServiceInterface;
use App\Interfaces\ICrud;
use App\Common\HttpStatusCodeHelper;
use Route;

class UserCardController extends Controller implements ICrud
{
    private $table_name;

    protected $userCardService;

    public function __construct(UserCardServiceInterface $userCardService)
    {
        $this->userCardService = $userCardService;

        $this->table_name = 'user_card';
    }

    /** ===== API METHOD ===== */
    public function getReadAll()
    {
        $arr_datas = $this->readAll();
        return response()->json($arr_datas, HttpStatusCodeHelper::$ok);
    }

    public function getReadOne()
    {
        $id  = Route::current()->parameter('id');
        $one = $this->readOne($id);
        return response()->json($one, HttpStatusCodeHelper::$ok);
    }

    public function postCreateOne(Request $request)
    {
        $data      = $request->input($this->table_name);

        /** Validate **/
        $validate_data = $this->createOne($data);
        if (!$validate_data['status'])
            return response()->json(['errors' => $validate_data['errors']], HttpStatusCodeHelper::$unprocessableEntity);

        $arr_datas = $this->readAll();
        return response()->json($arr_datas, HttpStatusCodeHelper::$created);
    }

    public function putUpdateOne(Request $request)
    {
        $data      = $request->input($this->table_name);

        /** Validate **/
        $validate_data = $this->updateOne($data);
        if (!$validate_data['status'])
            return response()->json(['errors' => $validate_data['errors']], HttpStatusCodeHelper::$unprocessableEntity);

        $arr_datas = $this->readAll();
        return response()->json($arr_datas, HttpStatusCodeHelper::$ok);
    }

    public function patchDeactivateOne(Request $request)
    {
        $id = $request->input('id');

        /** Validate **/
        $validate_data = $this->deactivateOne($id);
        if (!$validate_data['status'])
            return response()->json(['errors' => $validate_data['errors']], HttpStatusCodeHelper::$unprocessableEntity);

        $arr_datas = $this->readAll();
        return response()->json($arr_datas, HttpStatusCodeHelper::$ok);
    }

    public function deleteDeleteOne(Request $request)
    {
        $id = Route::current()->parameter('id');

        /** Validate **/
        $validate_data = $this->deleteOne($id);
        if (!$validate_data['status'])
            return response()->json(['errors' => $validate_data['errors']], HttpStatusCodeHelper::$unprocessableEntity);

        $arr_datas = $this->readAll();
        return response()->json($arr_datas, HttpStatusCodeHelper::$ok);
    }

    public function getSearchOne()
    {
        $filter    = (array)json_decode($_GET['query']);
        $arr_datas = $this->searchOne($filter);
        return response()->json($arr_datas, HttpStatusCodeHelper::$ok);
    }

    /** ===== LOGIC METHOD ===== */
    public function readAll()
    {
        return $this->userCardService->readAll();
    }

    public function readOne($id)
    {
        return $this->userCardService->readOne($id);
    }

    public function createOne($data)
    {
        return $this->userCardService->createOne($data);
    }

    public function updateOne($data)
    {
        return $this->userCardService->updateOne($data);
    }

    public function deactivateOne($id)
    {
        return $this->userCardService->deactivateOne($id);
    }

    public function deleteOne($id)
    {
        return $this->userCardService->deleteOne($id);
    }

    public function searchOne($filter)
    {
        return $this->userCardService->searchOne($filter);
    }

    /** ===== MY FUNCTION ===== */
    public function postCreateOrUpdateOne(Request $request)
    {
        $data = $request->input($this->table_name);

        /** Validate **/
        $validate_data = $this->createOrUpdateOne($data);
        if (!$validate_data['status'])
            return response()->json(['errors' => $validate_data['errors']], HttpStatusCodeHelper::$unprocessableEntity);

        $arr_datas = $this->readAll();
        return response()->json($arr_datas, 200);
    }

    public function createOrUpdateOne($data)
    {
        return $this->userCardService->createOrUpdateOne($data);
    }
}
