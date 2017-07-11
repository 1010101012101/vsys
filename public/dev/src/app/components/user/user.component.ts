import {Component, OnInit} from '@angular/core';

import {HttpClientService} from '../../services/httpClient.service';
import {DateHelperService} from '../../services/helpers/date.helper';
import {ToastrHelperService} from '../../services/helpers/toastr.helper';
import {DomHelperService} from '../../services/helpers/dom.helper';

@Component({
    selector: 'app-user',
    templateUrl: './user.component.html',
    styles: []
})
export class UserComponent implements OnInit
    , ICommon, ICrud, IDatePicker, ISearch {

    /** My Variables **/
    public positions: any[] = [];
    public positions_sup: any[] = [];
    public positions_dis: any[] = [];
    public distributors: any[] = [];
    public suppliers: any[] = [];
    public users: any[] = [];
    public users_search: any[] = [];
    public user: any;
    public birthday: Date;
    public fake_pwd: string = '';
    public auth: boolean = false;

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
        , private domHelperService: DomHelperService) {
    }

    ngOnInit(): void {
        this.title = 'Người dùng';
        this.prefix_url = 'users';
        this.range_date = this.dateHelperService.range_date;
        this.refreshData();
        this.datepickerSettings = this.dateHelperService.datepickerSettings;
        this.header = {
            code: {
                title: 'Mã',
                data_type: 'TEXT'
            },
            fullname: {
                title: 'Tên',
                data_type: 'TEXT'
            },
            position_name: {
                title: 'Chức vụ',
                data_type: 'TEXT'
            },
            username: {
                title: 'Tài khoản',
                data_type: 'TEXT'
            },
            fc_total_money: {
                title: 'Số dư TK',
                data_type: 'NUMBER',
                prop_name: 'total_money'
            },
            address: {
                title: 'Địa chỉ',
                data_type: 'TEXT'
            },
            phone: {
                title: 'Điện thoại',
                data_type: 'TEXT'
            },
            sex: {
                title: 'Giới tính',
                data_type: 'TEXT'
            },
            email: {
                title: 'Email',
                data_type: 'TEXT'
            },
            note: {
                title: 'Ghi chú',
                data_type: 'TEXT'
            },
            supplier_name: {
                title: 'Khách hàng',
                data_type: 'TEXT'
            },
            distributor_name: {
                title: 'Đại lý',
                data_type: 'TEXT'
            }
        };
        this.modal = {
            id: 0,
            header: '',
            body: '',
            footer: ''
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
        this.auth = arr_data['auth'];
        this.fake_pwd = arr_data['fake_pwd'];

        this.users = [];
        this.users_search = arr_data['users'];

        this.positions = arr_data['positions'];
        this.positions_sup = this.positions.filter(function (o) {
            return [3, 4].includes(o.id);
        });
        this.positions_dis = this.positions.filter(function (o) {
            return [3, 6].includes(o.id);
        });

        this.suppliers = arr_data['suppliers'];

        this.distributors = arr_data['distributors'];

        this.placeholder_code = arr_data['placeholder_code'];
    }

    refreshData(): void {
        this.changeLoading(false);
        this.clearOne();
        this.clearSearch();
        this.loadData();
    }

    changeLoading(status: boolean): void {
        this.isLoading = status;
    }

    /** ICrud **/
    loadOne(id: number): void {
        this.user = this.users.find(function (o) {
            return o.id == id;
        });

        this.birthday = this.dateHelperService.createDate(this.user.birthday);
        this.user.password = this.fake_pwd;

        this.isEdit = true;

        this.domHelperService.showTab('menu2');
    }

    clearOne(): void {
        this.user = {
            code: '',
            fullname: '',
            username: '',
            password: '',
            address: '',
            phone: '',
            birthday: '',
            sex: 'Nam',
            email: '',
            note: '',
            position_id: 0,
            dis_or_sup: 'sup',
            dis_or_sup_id: 0,
            active: true
        };
        this.birthday = null;
    }

    addOne(): void {
        if (!this.validateOne()) return;

        // this.user.birthday = this.utilitiesService.getDate(this.birthday);

        this.httpClientService.post(this.prefix_url, {"user": this.user}).subscribe(
            (success: any) => {
                this.reloadData(success);
                this.clearOne();
                this.toastrHelperService.showToastr('success', 'Thêm thành công.');
            },
            (error: any) => {
                this.toastrHelperService.showToastrErrors(error.json());
            }
        );
    }

    updateOne(): void {
        if (!this.validateOne()) return;

        // this.user.birthday = this.utilitiesService.getDate(this.birthday);

        this.httpClientService.put(this.prefix_url, {"user": this.user}).subscribe(
            (success: any) => {
                this.reloadData(success);
                this.clearOne();
                this.displayEditBtn(false);
                this.toastrHelperService.showToastr('success', 'Cập nhật thành công.');
            },
            (error: any) => {
                this.toastrHelperService.showToastrErrors(error.json());
            }
        );
    }

    deactivateOne(id: number): void {
        this.httpClientService.patch(this.prefix_url, {"id": id}).subscribe(
            (success: any) => {
                this.reloadData(success);
                this.search();
                this.toastrHelperService.showToastr('success', 'Hủy thành công.');
                this.domHelperService.toggleModal('modal-confirm');
            },
            (error: any) => {
                this.toastrHelperService.showToastrErrors(error.json());
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
                this.toastrHelperService.showToastrErrors(error.json());
            }
        );
    }

    confirmDeactivateOne(id: number): void {
        this.deactivateOne(id);
    }

    validateOne(): boolean {
        let flag: boolean = true;
        if (this.user.fullname == '') {
            flag = false;
            this.toastrHelperService.showToastr('warning', `Họ tên ${this.title} không được để trống.`);
        }
        if (this.user.position_id == 0) {
            flag = false;
            this.toastrHelperService.showToastr('warning', 'Chức vụ không được để trống.');
        }
        if (this.user.dis_or_sup == '') {
            flag = false;
            this.toastrHelperService.showToastr('warning', 'Loại khách hàng không được để trống.');
        }
        if (this.user.dis_or_sup_id == 0) {
            flag = false;
            this.toastrHelperService.showToastr('warning', 'Vui lòng chọn khách hàng hoặc đại lý.');
        }
        // if (this.birthday == null) {
        //     flag = false;
        //     this.utilitiesService.showToastr('warning', 'Ngày sinh không được để trống.');
        // }
        return flag;
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
        this.users = arr_data['users'];
    }

    clearSearch(): void {
        this.datepicker_from = null;
        this.datepicker_to = null;
        this.filtering = {
            from_date: '',
            to_date: '',
            range: '',
            dis_or_sup: 'sup',
            supplier_id: 0,
            distributor_id: 0,
            position_id: 0,
            code: '',
            fullname: '',
            username: '',
            phone: '',
        };
    }

    displayColumn(): void {
        let setting = {
            supplier_id: ['supplier_name'],
            distributor_id: ['distributor_name'],
            position_id: ['position_name'],
            code: ['code'],
            username: ['username'],
            fullname: ['fullname'],
            phone: ['phone'],
        };
        for (let parent in setting) {
            for (let child of setting[parent]) {
                if (!!this.header[child])
                    this.header[child].visible = !(!!this.filtering[parent]);
            }
        }

        this.header.supplier_name.visible = this.filtering.dis_or_sup == 'sup';
        this.header.distributor_name.visible = this.filtering.dis_or_sup == 'dis';
    }

    /** My Function **/
}
