import {Component, OnInit} from '@angular/core';

import {HttpClientService} from '../../../services/httpClient.service';
import {DateHelperService} from '../../../services/helpers/date.helper';
import {ToastrHelperService} from '../../../services/helpers/toastr.helper';
import {DomHelperService} from '../../../services/helpers/dom.helper';
import {FileHelperService} from '../../../services/helpers/file.helper';
import {DeviceCaptionService} from '../../../services/captions/device.caption';

@Component({
    selector: 'app-report-supplier-stock',
    templateUrl: './stock.component.html'
})
export class ReportSupplierStockComponent implements OnInit
    , ICommon, ICrud, IDatePicker, ISearch {

    /** My Variables **/
    public header_stock: any;

    public report_stocks: any[] = [];
    public supplier: any = null;
    public suppliers: any[] = [];
    public distributors: any[] = [];
    public staffs: any[] = [];
    public products: any[] = [];
    public producers: any[] = [];
    public product_types: any[] = [];
    public units: any[] = [];
    public cabinets: any[] = [];
    public first_day: string = '';
    public last_day: string = '';
    public today: string = '';
    public filter_ReportStock: any;
    public isLoading_Stock: boolean = true;

    public _deviceCaptionService;

    /** ICommon **/
    title: string;
    placeholder_code: string;
    prefix_url: string;
    isLoading: boolean;
    header: any;
    action_data: any;

    /** ICrud **/
    modal: any;
    isEdit: boolean;

    /** IDatePicker **/
    range_date: any[];
    datepickerSettings: any;
    datepicker_from: Date;
    datepicker_to: Date;
    datepickerToOpts: any = {};

    /** ISearch **/
    filtering: any;

    constructor(private httpClientService: HttpClientService
        , private dateHelperService: DateHelperService
        , private toastrHelperService: ToastrHelperService
        , private domHelperService: DomHelperService
        , private fileHelperService: FileHelperService
        , private deviceCaptionService: DeviceCaptionService) {
        this._deviceCaptionService = this.deviceCaptionService;
    }

    ngOnInit(): void {
        this.title = 'Báo cáo khách hàng';
        this.prefix_url = 'report-suppliers';
        this.range_date = this.dateHelperService.range_date;
        this.refreshData();
        this.datepickerSettings = this.dateHelperService.datepickerSettings;
        this.action_data = {
            ADD: false,
            EDIT: false,
            DELETE: false
        };
        this.header_stock = {
            distributor_name: {
                title: 'Đại lý',
                data_type: 'TEXT'
            },
            cabinet_name: {
                title: this._deviceCaptionService.cabinet,
                data_type: 'TEXT'
            },
            tray_name: {
                title: this._deviceCaptionService.tray,
                data_type: 'TEXT'
            },
            product_barcode: {
                title: 'Mã vạch SP',
                data_type: 'TEXT'
            },
            product_name: {
                title: 'Tên sản phẩm',
                data_type: 'TEXT'
            },
            unit_name: {
                title: 'ĐVT',
                data_type: 'TEXT'
            },
            TonDauKy: {
                title: 'Tồn đầu kỳ',
                data_type: 'NUMBER'
            },
            sum_in: {
                title: 'Nhập trong kỳ',
                data_type: 'NUMBER'
            },
            sum_out: {
                title: 'Xuất trong kỳ',
                data_type: 'NUMBER'
            },
            quantum_remain: {
                title: 'Tồn cuối kỳ',
                data_type: 'NUMBER'
            }
        };
    }

    /** ICommon **/
    loadData(): void {
        this.httpClientService.get(this.prefix_url).subscribe(
            (success: any) => {
                this.reloadData(success);
                this.changeLoading(true);
            },
            (error: any) => {
                this.toastrHelperService.showToastr('error');
            }
        );
    }

    reloadData(arr_data: any[]): void {
        this.first_day = arr_data['first_day'];
        this.last_day = arr_data['last_day'];
        this.today = arr_data['today'];

        this.supplier = arr_data['supplier'];

        this.suppliers = arr_data['suppliers'];

        this.distributors = arr_data['distributors'];

        this.staffs = arr_data['staffs'];

        this.products = arr_data['products'];

        this.producers = arr_data['producers'];

        this.product_types = arr_data['product_types'];

        this.units = arr_data['units'];

        this.cabinets = arr_data['cabinets'];
    }

    refreshData(): void {
        this.changeLoading(false);
        this.clearOne();
        this.clearSearch();
        this.clearSearchReportStock();
        this.loadData();
    }

    changeLoading(status: boolean): void {
        this.isLoading = status;
    }

    /** ICrud **/
    loadOne(id: number): void {
    }

    clearOne(): void {
    }

    addOne(): void {
    }

    updateOne(): void {
    }

    deactivateOne(id: number): void {
        this.httpClientService.patch(this.prefix_url, {"id": id}).subscribe(
            (success: any) => {
                this.reloadData(success);
                this.toastrHelperService.showToastr('success', 'Hủy thành công.');
                this.domHelperService.toggleModal('modal-confirm');
            },
            (error: any) => {
                this.toastrHelperService.showToastr('error');
            }
        );
    }

    deleteOne(id: number): void {
        this.httpClientService.delete(`${this.prefix_url}/${id}`).subscribe(
            (success: any) => {
                this.reloadData(success);
                this.toastrHelperService.showToastr('success', 'Xóa thành công!');
            },
            (error: any) => {
                this.toastrHelperService.showToastr('error');
            }
        );
    }

    confirmDeactivateOne(id: number): void {
        this.deactivateOne(id);
    }

    validateOne(): boolean {
        return null;
    }

    displayEditBtn(status: boolean): void {
        this.isEdit = status;
    }

    fillDataModal(id: number): void {
        this.modal.id = id;
        this.modal.header = 'Xác nhận';
        this.modal.body = `Có chắc muốn xóa ${this.title} này?`;
        this.modal.footer = 'OK';
    }

    actionCrud(obj: any): void {
        switch (obj.mode) {
            case 'ADD':
                this.displayEditBtn(false);
                this.clearOne();
                this.domHelperService.showTab('menu2');
                break;
            case 'EDIT':
                this.loadOne(obj.data.id);
                break;
            case 'DELETE':
                this.fillDataModal(obj.data.id);
                break;
            default:
                break;
        }
    }

    /** IDatePicker **/
    handleDateFromChange(dateFrom: Date): void {
        this.datepicker_from = dateFrom;
        this.datepickerToOpts = {
            startDate: dateFrom,
            autoclose: true,
            todayBtn: 'linked',
            todayHighlight: true,
            icon: this.dateHelperService.icon_calendar,
            placeholder: this.dateHelperService.date_placeholder,
            format: 'dd/mm/yyyy'
        };
    }

    clearDate(event: any, field: string): void {
        if (event == null) {
            switch (field) {
                case 'from':
                    this.filtering.from_date = '';
                    break;
                case 'to':
                    this.filtering.from_date = '';
                    break;
                default:
                    break;
            }
        }
    }

    /** ISearch **/
    search(): void {
        if (this.datepicker_from != null && this.datepicker_to != null) {
            let from_date = this.dateHelperService.getDate(this.datepicker_from);
            let to_date = this.dateHelperService.getDate(this.datepicker_to);
            this.filtering.from_date = from_date;
            this.filtering.to_date = to_date;
        }
        this.changeLoading(false);

        this.httpClientService.get(`${this.prefix_url}/search?query=${JSON.stringify(this.filtering)}`).subscribe(
            (success: any) => {
                this.reloadDataSearch(success);
                this.displayColumn();
                this.changeLoading(true);
            },
            (error: any) => {
                this.toastrHelperService.showToastr('error');
            }
        );
    }

    reloadDataSearch(arr_data: any[]): void {
    }

    clearSearch(): void {
    }

    displayColumn(): void {
    }

    /** My Function **/

    /** Search Report Stock */
    public search_ReportStock() {
        this.myChangeLoading('stock', false);

        this.filter_ReportStock.show_type = 'web';

        this.httpClientService.get(`${this.prefix_url}/report-stocks/search?query=${JSON.stringify(this.filter_ReportStock)}`).subscribe(
            (success: any) => {
                this.reloadDataReportStock(success);
                this.myDisplayColumn('stock');
                this.myChangeLoading('stock', true);
            },
            (error: any) => {
                this.toastrHelperService.showToastr('error');
            }
        );
    }

    private reloadDataReportStock(arr_datas): void {
        this.report_stocks = arr_datas['report_stocks'];
    }

    public clearSearchReportStock(): void {
        this.filter_ReportStock = {
            month: new Date().getMonth() + 1,
            year: new Date().getFullYear(),
            product_id: 0,
            unit_id: 0,
            distributor_id: 0,
            cabinet_id: 0,
            show_type: ''
        };
    }

    public myChangeLoading(type: string, status: boolean): void {
        switch (type) {
            case 'stock':
                this.isLoading_Stock = status;
                break;
            default:
                break;
        }
    }

    public onMonthChange(event: any) {
        let str_month: string = event.target.value;
        let month: number = Number(str_month.replace('Tháng ', ''));
        this.filter_ReportStock.month = month;
    }

    public onYearChange(event: any) {
        let year = event.target.value;
        this.filter_ReportStock.year = year;
    }

    public myDisplayColumn(mode: string) {
        let setting = {
            product_id: ['product_barcode', 'product_name'],
            unit_id: ['unit_name'],
            distributor_id: ['distributor_name'],
            staff_input_id: ['staff_input_name'],
            staff_output_id: ['staff_output_name'],
            cabinet_id: ['cabinet_name'],
            adjust_by: ['adjuster_fullname']
        };
        switch (mode) {
            case 'stock':
                for (let parent in setting) {
                    for (let child of setting[parent]) {
                        if (!!this.header_stock[child])
                            this.header_stock[child].visible = !(!!this.filter_ReportStock[parent]);
                    }
                }
                break;
            default:
                break;
        }
    }

    public downloadFile(mode: string) {
        let subfix_filename: string = '';
        let url: string = '';
        switch (mode) {
            case 'stock':
                subfix_filename = 'Ton';
                this.filter_ReportStock.show_type = 'csv';
                url = `report-stocks/search?query=${JSON.stringify(this.filter_ReportStock)}`;
                break;
            default: break;
        }
        this.httpClientService.get(`${this.prefix_url}/${url}`, 'text')
            .subscribe(
                (success: any) => {
                    this.fileHelperService.downloadFile(success, `BaoCaoKhachHang_${subfix_filename}.csv`, 'text/csv');
                },
                (error: any) => {
                    this.toastrHelperService.showToastr('error');
                }
            );
    }
}
