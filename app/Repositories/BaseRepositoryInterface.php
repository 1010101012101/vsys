<?php

namespace App\Repositories;

interface BaseRepositoryInterface
{
    public function findOne($id);
    public function findAll();
    public function findAllByIds($ids);
    public function findOneByFieldName($field_name, $value, $operator = '=');
    public function findAllByFieldName($field_name, $value, $operator = '=');

    // Active
    public function findOneActive($id);
    public function findAllActive();
    public function findAllActiveByIds($ids);
    public function findOneActiveByFieldName($field_name, $value, $operator = '=');
    public function findAllActiveByFieldName($field_name, $value, $operator = '=');

    public function createOne($data);
    public function updateOne($model, $data);
    public function saveOne($data);

    public function deactivateOne($id);
    public function deactivateAll();
    public function deactivateAllByIds($ids);

    public function destroyOne($id);
    public function destroyAll();
    public function destroyAllByIds($ids);

    public function countAll();
    public function countAllActive();

    public function generateCode($prefix);
    public function existsValue($field_name, $value, $skip_id = []);
}