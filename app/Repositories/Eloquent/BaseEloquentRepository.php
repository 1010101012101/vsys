<?php

namespace App\Repositories\Eloquent;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

use App\Repositories\BaseRepositoryInterface;
use App\Common\DateTimeHelper;
use Carbon\Carbon;

abstract class BaseEloquentRepository implements BaseRepositoryInterface
{
    protected $model;

    public function __construct()
    {
        $this->getModel();
    }

    abstract function setModel();

    private function getModel()
    {
        $model       = $this->setModel();
        $this->model = app()->make($model);
    }

    /**  **/
    public function findOne($id)
    {
        return $this->model->find($id);
    }

    public function findAll()
    {
        return $this->model->all();
    }

    public function findAllByIds($ids)
    {
        return $this->model->whereIn('id', $ids)
            ->get();
    }

    public function findOneByFieldName($field_name, $value, $operator = '=')
    {
        return $this->model
            ->where($field_name, $operator, $value)
            ->first();
    }

    public function findAllByFieldName($field_name, $value, $operator = '=')
    {
        return $this->model
            ->where($field_name, $operator, $value)
            ->get();
    }

    /**  **/
    public function findOneActive($id)
    {
        return $this->model->whereActive(true)
            ->where('id', $id)
            ->first();
    }

    public function findAllActive()
    {
        return $this->model->whereActive(true)
            ->get();
    }

    public function findAllActiveByIds($ids)
    {
        return $this->model->whereActive(true)
            ->whereIn('id', $ids)
            ->get();
    }

    public function findOneActiveByFieldName($field_name, $value, $operator = '=')
    {
        return $this->model->whereActive(true)
            ->where($field_name, $operator, $value)
            ->first();
    }

    public function findAllActiveByFieldName($field_name, $value, $operator = '=')
    {
        return $this->model->whereActive(true)
            ->where($field_name, $operator, $value)
            ->get();
    }

    /**  **/
    public function createOne($data)
    {
        return $this->model->create($data); // object
    }

    public function updateOne($model, $data)
    {
        $model->update($data);
        return $model; // object
    }

    public function saveOne($data)
    {
        return $this->model->save($data); // object
    }

    public function deactivateOne($id)
    {
        return $this->model->find($id)->update(['active' => false]); // boolean
    }

    public function deactivateAll()
    {
        return $this->model->whereActive(true)->update(['active' => false]); // number
    }

    public function deactivateAllByIds($ids)
    {
        return $this->model->whereIn('id', $ids)->update(['active' => false]); // number
    }

    public function destroyOne($id)
    {
        return $this->model->destroy($id); // number
    }

    public function destroyAll()
    {
        return $this->model->whereActive(true)->delete(); // number
    }

    public function destroyAllByIds($ids)
    {
        return $this->model->destroy($ids); // number
    }

    public function countAll()
    {
        return $this->model->count(); // number
    }

    public function countAllActive()
    {
        return $this->model->whereActive(true)->count(); // number
    }

    public function generateCode($prefix)
    {
        $code = $prefix . date('ymd');
        $stt  = $this->model->where('code', 'like', $code . '%')->get()->count() + 1;
        return $code . substr("00" . $stt, -3);
    }

    public function existsValue($field_name, $value, $skip_id = [])
    {
        // Check luôn cả dữ liệu đã deactivate [whereActive(true)]
        $exists = $this->model->where($field_name, $value)->whereNotIn('id', $skip_id)->get();
        return ($exists->count() > 0); // boolean
    }

}