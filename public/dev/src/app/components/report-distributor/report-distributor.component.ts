import {Component, OnInit} from '@angular/core';

@Component({
    selector: 'app-report-distributor',
    templateUrl: './report-distributor.component.html',
    styles: []
})
export class ReportDistributorComponent implements OnInit {

    /** My Variables **/
    public title: string;

    constructor() {
    }

    ngOnInit(): void {
        this.title = 'Báo cáo đại lý';

    }
}
