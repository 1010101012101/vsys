import {Component, OnInit} from '@angular/core';

@Component({
    selector: 'app-report-supplier',
    templateUrl: './report-supplier.component.html'
})
export class ReportSupplierComponent implements OnInit {
    /** My Variables **/
    public title: string;

    constructor() {
    }

    ngOnInit(): void {
        this.title = 'Báo cáo khách hàng';

    }
}
