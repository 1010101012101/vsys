<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Collection;
use DB;
use Excel;
use App\HistoryInputOutput;
use App\Traits\DBHelper;

class FileController extends Controller
{

    use DBHelper;

    public function __construct()
    {
    }

    public function getImportExport()
    {
        return view('files.import-export');
    }

    public function getDownload($type)
    {
//		return Excel::create('tintansoft_example', function($excel) use ($data) {
//			$excel->sheet('mySheet', function($sheet) use ($data)
//	        {
////				$sheet->fromArray($data);
////                $sheet->loadView('files.demo', array('data' => $data));
//
//                // first row styling and writing content
//                $sheet->mergeCells('A1:W1');
//                $sheet->row(1, function ($row) {
//                    $row->setFontFamily('Comic Sans MS');
//                    $row->setFontSize(30);
//                });
//
//                $sheet->row(1, array('Some big header here'));
//
//                // second row styling and writing content
//                $sheet->row(2, function ($row) {
//
//                    // call cell manipulation methods
//                    $row->setFontFamily('Comic Sans MS');
//                    $row->setFontSize(15);
//                    $row->setFontWeight('bold');
//
//                });
//
//                $sheet->row(2, array('Something else here'));
//
//                // getting data to display - in my case only one record
//
//                // setting column names for data - you can of course set it manually
//                $sheet->appendRow(array_keys($data[0])); // column names
//
//                // getting last row number (the one we already filled and setting it to bold
//                $sheet->row($sheet->getHighestRow(), function ($row) {
//                    $row->setFontWeight('bold');
//                });
//
//                // putting users data as next rows
//                foreach ($data as $user) {
//                    $sheet->appendRow($user);
//                }
//	        });
//        })->download($type);


        $report = HistoryInputOutput::where([['history_input_outputs.active', true], ['history_input_outputs.status', 'OUT'], ['history_input_outputs.isDefault', false]])
            ->leftJoin('devices', 'devices.id', '=', 'history_input_outputs.button_id')
            ->leftJoin('devices as cabinets', 'cabinets.id', '=', 'devices.parent_id')
            ->leftJoin('products', 'products.id', '=', 'history_input_outputs.product_id')
            ->leftJoin('units', 'units.id', '=', 'products.unit_id')
            ->leftJoin('distributors', 'distributors.id', '=', 'history_input_outputs.dis_id')
            ->leftJoin('suppliers', 'suppliers.id', '=', 'distributors.sup_id')
            ->leftJoin('users as staff_output', 'staff_output.id', '=', 'history_input_outputs.user_output_id')
            ->leftJoin('users as adjuster', 'adjuster.id', '=', 'history_input_outputs.adjust_by')
            ->select('history_input_outputs.total_pay'
                , 'history_input_outputs.total_pay'
                , DB::raw($this->getWithCurrencyFormat('history_input_outputs.total_pay', 'fc_total_pay'))
                , 'history_input_outputs.product_price'
                , DB::raw($this->getWithCurrencyFormat('history_input_outputs.product_price', 'fc_product_price'))
                , 'history_input_outputs.quantum_out', 'history_input_outputs.product_price'
                , 'devices.id as tray_id', 'devices.name as tray_name'
                , 'cabinets.id as cabinet_id', 'cabinets.code as cabinet_code', 'cabinets.name as cabinet_name'
                , 'products.id as product_id', 'products.name as product_name', 'products.barcode as product_barcode'
                , 'units.id as unit_id', 'units.name as unit_name'
                , 'distributors.id as distributor_id', 'distributors.name as distributor_name'
                , 'suppliers.id as supplier_id', 'suppliers.name as supplier_name'
                , 'staff_output.id as staff_output_id', 'staff_output.fullname as staff_output_fullname', 'staff_output.phone as staff_output_phone'
                , 'adjuster.id as adjuster_id', 'adjuster.fullname as adjuster_fullname', 'adjuster.phone as adjuster_phone'
                , DB::raw($this->getWithDateTimeFormat('history_input_outputs.created_date', 'datetime_output'))
                , DB::raw($this->getWithDateFormat('history_input_outputs.created_date', 'date_output'))
                , DB::raw($this->getWithTimeFormat('history_input_outputs.created_date', 'time_output'))
            )
            ->orderBy('history_input_outputs.created_date', 'desc')
            ->get();

        $report_total    = $report->groupBy('date_output');
        $report_supplier = $report->groupBy('supplier_id');
        $report_product  = $report->groupBy('product_id');
        $report_cabinet  = $report->groupBy('cabinet_id');

        return Excel::create('Filename', function ($excel) use ($report_total, $report_supplier, $report_product, $report_cabinet) {

            $excel->sheet('Sheetname', function ($sheet) use ($report_total, $report_supplier, $report_product, $report_cabinet) {

                $sheet->loadView('reports.total2', [
                    'report_total'    => $report_total,
                    'report_supplier' => $report_supplier,
                    'report_product'  => $report_product,
                    'report_cabinet'  => $report_cabinet
                ]);

            });

        })->export($type);
    }

    public function postImport(Request $request)
    {
        if ($request->hasFile('import_file')) {
            $path = $request->file('import_file')->getRealPath();
            $data = Excel::load($path, function ($reader) {
            })->get();
            if (!empty($data) && $data->count()) {
                foreach ($data as $key => $value) {
                    $insert[] = ['name' => $value->name, 'description' => $value->description];
                }
                if (!empty($insert)) {
                    DB::table('collections')->insert($insert);
                    dd('Insert Record successfully.');
                }
            }
        }
        return back();
    }

    // Document Laravel-Excel
//        Loading a view for a single sheet
//        $sheet->loadView('folder.view');
//
//        Sharing a view for all sheets
//        Excel::shareView('folder.view')->create();
//
//        Unsetting a view for a sheet
//        $sheet->unsetView();
//
//        Passing variables to the view
//        As parameter
//        $sheet->loadView('view', array('key' => 'value'));
//        With with()
//        Using normal with()
//        $sheet->loadView('view')->with('key', 'value');
//        using dynamic with()
//        $sheet->loadView('view')->withKey('value');
}
