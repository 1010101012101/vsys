<?php

namespace App\Services;


interface ReportInputSaleStockServiceInterface
{
    public function readAll();

    public function readStock();
    public function readStockByUser($report_stocks);
    public function readStockBySearch($filter);

    public function readBySearch($filter, $mode);

    public function readInput();
    public function readInputByUser($report_inputs);

    public function readSale();
    public function readSaleByUser($report_sales);

    public function changeColumnName($data, $mode);

    public function readTotal();
    public function readTotalByUser($filter);

}