<table>
    <tr>
        @if($dis_or_sup == 'dis')
            <td>BÁO CÁO CHO ĐẠI LÝ</td>
        @elseif($dis_or_sup == 'sup')
            <td>BÁO CÁO CHO KHÁCH HÀNG</td>
        @else
            <td>BÁO CÁO CHO ADMIN</td>
        @endif
        <td>Từ {{ $from_date }} đến {{ $to_date }}</td>
        <td></td>
        <td></td>
        <td></td>
        <td></td>
        <td></td>
        <td></td>
        <td></td>
    </tr>

    <tr>
        <td></td>
        <td></td>
        <td></td>
        <td></td>
        <td></td>
        <td></td>
        <td></td>
        <td></td>
        <td></td>
    </tr>

    {{-- /////////////////////////////////////////////////// --}}

    <tr>
        <td>1. Báo cáo tổng hợp</td>
        <td></td>
        <td></td>
        <td></td>
        <td></td>
        <td></td>
        <td></td>
        <td></td>
        <td></td>
    </tr>

    <tr>
        <td></td>
        <td></td>
        <td></td>
        <td></td>
        <td></td>
        <td></td>
        <td></td>
        <td></td>
        <td></td>
    </tr>

    <tr>
        <td>Ngày</td>
        <td>Giờ</td>
        <td>Khách hàng</td>
        <td>Mã máy</td>
        <td>Mã sản phẩm</td>
        <td>Tên sản phẩm</td>
        <td>Số lượng</td>
        <td>Đơn giá</td>
        <td>Thành tiền</td>
    </tr>

    @foreach($report_total as $key_by_date => $report_by_date)
        @foreach($report_by_date as $key => $report)
            <tr>
                <td>{{ $key == 0 ? $report->date_output : '' }}</td>
                <td>{{ $report->time_output }}</td>
                <td>{{ $report->supplier_name }}</td>
                <td>{{ $report->cabinet_code }}</td>
                <td>{{ $report->product_barcode }}</td>
                <td>{{ $report->product_name }}</td>
                <td>{{ $report->quantum_out }}</td>
                <td>{{ $report->product_price }}</td>
                <td>{{ $report->total_pay }}</td>
            </tr>
        @endforeach
        <tr>
            <td><b>Tổng</b></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td><b>{{ $report_by_date->sum('quantum_out') }}</b></td>
            <td></td>
            <td><b>{{ $report_by_date->sum('total_pay') }}</b></td>
        </tr>
    @endforeach

    <tr>
        <td></td>
        <td></td>
        <td></td>
        <td></td>
        <td></td>
        <td></td>
        <td></td>
        <td></td>
        <td></td>
    </tr>

    <tr>
        <td></td>
        <td></td>
        <td></td>
        <td></td>
        <td></td>
        <td></td>
        <td></td>
        <td></td>
        <td></td>
    </tr>

    {{-- /////////////////////////////////////////////////// --}}

    @if($dis_or_sup != 'sup' && $dis_or_sup != 'dis')
    <tr>
        <td>2. Báo cáo theo khách hàng</td>
        <td></td>
        <td></td>
        <td></td>
        <td></td>
        <td></td>
        <td></td>
        <td></td>
        <td></td>
    </tr>

    <tr>
        <td></td>
        <td></td>
        <td></td>
        <td></td>
        <td></td>
        <td></td>
        <td></td>
        <td></td>
        <td></td>
    </tr>

    <tr>
        <td>Khách hàng</td>
        <td>Ngày</td>
        <td>Giờ</td>
        <td>Mã máy</td>
        <td>Mã sản phẩm</td>
        <td>Tên sản phẩm</td>
        <td>Số lượng</td>
        <td>Đơn giá</td>
        <td>Thành tiền</td>
    </tr>

    @foreach($report_supplier as $key_by_supplier => $report_by_supplier)
        @foreach($report_by_supplier as $key => $report)
            <tr>
                <td>{{ $key == 0 ? $report->supplier_name : ''}}</td>
                <td>{{ $report->date_output }}</td>
                <td>{{ $report->time_output }}</td>
                <td>{{ $report->cabinet_code }}</td>
                <td>{{ $report->product_barcode }}</td>
                <td>{{ $report->product_name }}</td>
                <td>{{ $report->quantum_out }}</td>
                <td>{{ $report->product_price }}</td>
                <td>{{ $report->total_pay }}</td>
            </tr>
        @endforeach
        <tr>
            <td><b>Tổng</b></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td><b>{{ $report_by_supplier->sum('quantum_out') }}</b></td>
            <td></td>
            <td><b>{{ $report_by_supplier->sum('total_pay') }}</b></td>
        </tr>
    @endforeach

    <tr>
        <td></td>
        <td></td>
        <td></td>
        <td></td>
        <td></td>
        <td></td>
        <td></td>
        <td></td>
        <td></td>
    </tr>

    <tr>
        <td></td>
        <td></td>
        <td></td>
        <td></td>
        <td></td>
        <td></td>
        <td></td>
        <td></td>
        <td></td>
    </tr>
    @endif

    {{-- /////////////////////////////////////////////////// --}}

    @if($dis_or_sup != 'dis')
    <tr>
        <td>2. Báo cáo theo đại lý</td>
        <td></td>
        <td></td>
        <td></td>
        <td></td>
        <td></td>
        <td></td>
        <td></td>
        <td></td>
    </tr>

    <tr>
        <td></td>
        <td></td>
        <td></td>
        <td></td>
        <td></td>
        <td></td>
        <td></td>
        <td></td>
        <td></td>
    </tr>

    <tr>
        <td>Đại lý</td>
        <td>Ngày</td>
        <td>Giờ</td>
        <td>Mã máy</td>
        <td>Mã sản phẩm</td>
        <td>Tên sản phẩm</td>
        <td>Số lượng</td>
        <td>Đơn giá</td>
        <td>Thành tiền</td>
    </tr>

    @foreach($report_distributor as $key_by_distributor => $report_by_distributor)
        @foreach($report_by_distributor as $key => $report)
            <tr>
                <td>{{ $key == 0 ? $report->distributor_name : ''}}</td>
                <td>{{ $report->date_output }}</td>
                <td>{{ $report->time_output }}</td>
                <td>{{ $report->cabinet_code }}</td>
                <td>{{ $report->product_barcode }}</td>
                <td>{{ $report->product_name }}</td>
                <td>{{ $report->quantum_out }}</td>
                <td>{{ $report->product_price }}</td>
                <td>{{ $report->total_pay }}</td>
            </tr>
        @endforeach
        <tr>
            <td><b>Tổng</b></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td><b>{{ $report_by_distributor->sum('quantum_out') }}</b></td>
            <td></td>
            <td><b>{{ $report_by_distributor->sum('total_pay') }}</b></td>
        </tr>
    @endforeach

    <tr>
        <td></td>
        <td></td>
        <td></td>
        <td></td>
        <td></td>
        <td></td>
        <td></td>
        <td></td>
        <td></td>
    </tr>

    <tr>
        <td></td>
        <td></td>
        <td></td>
        <td></td>
        <td></td>
        <td></td>
        <td></td>
        <td></td>
        <td></td>
    </tr>
    @endif

    {{-- /////////////////////////////////////////////////// --}}

    <tr>
        <td>3. Báo cáo theo sản phẩm</td>
        <td></td>
        <td></td>
        <td></td>
        <td></td>
        <td></td>
        <td></td>
        <td></td>
        <td></td>
    </tr>

    <tr>
        <td></td>
        <td></td>
        <td></td>
        <td></td>
        <td></td>
        <td></td>
        <td></td>
        <td></td>
        <td></td>
    </tr>

    <tr>
        <td>Mã sản phẩm</td>
        <td>Tên sản phẩm</td>
        <td>Ngày</td>
        <td>Giờ</td>
        <td>Mã máy</td>
        <td>Khách hàng</td>
        <td>Số lượng</td>
        <td>Đơn giá</td>
        <td>Thành tiền</td>
    </tr>

    @foreach($report_product as $key_by_product => $report_by_product)
        @foreach($report_by_product as $key => $report)
            <tr>
                <td>{{ $key == 0 ? $report->product_barcode : ''}}</td>
                <td>{{ $report->product_name }}</td>
                <td>{{ $report->date_output }}</td>
                <td>{{ $report->time_output }}</td>
                <td>{{ $report->cabinet_code }}</td>
                <td>{{ $report->supplier_name }}</td>
                <td>{{ $report->quantum_out }}</td>
                <td>{{ $report->product_price }}</td>
                <td>{{ $report->total_pay }}</td>
            </tr>
        @endforeach
        <tr>
            <td><b>Tổng</b></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td><b>{{ $report_by_product->sum('quantum_out') }}</b></td>
            <td></td>
            <td><b>{{ $report_by_product->sum('total_pay') }}</b></td>
        </tr>
    @endforeach

    <tr>
        <td></td>
        <td></td>
        <td></td>
        <td></td>
        <td></td>
        <td></td>
        <td></td>
        <td></td>
        <td></td>
    </tr>

    <tr>
        <td></td>
        <td></td>
        <td></td>
        <td></td>
        <td></td>
        <td></td>
        <td></td>
        <td></td>
        <td></td>
    </tr>

    {{-- /////////////////////////////////////////////////// --}}

    <tr>
        <td>4. Báo cáo theo tủ</td>
        <td></td>
        <td></td>
        <td></td>
        <td></td>
        <td></td>
        <td></td>
        <td></td>
        <td></td>
    </tr>

    <tr>
        <td></td>
        <td></td>
        <td></td>
        <td></td>
        <td></td>
        <td></td>
        <td></td>
        <td></td>
        <td></td>
    </tr>

    <tr>
        <td>Mã máy</td>
        <td>Mã sản phẩm</td>
        <td>Tên sản phẩm</td>
        <td>Ngày</td>
        <td>Giờ</td>
        <td>Khách hàng</td>
        <td>Số lượng</td>
        <td>Đơn giá</td>
        <td>Thành tiền</td>
    </tr>

    @foreach($report_cabinet as $key_by_cabinet => $report_by_cabinet)
        @foreach($report_by_cabinet as $key => $report)
            <tr>
                <td>{{ $key == 0 ? $report->cabinet_code : ''}}</td>
                <td>{{ $report->product_barcode }}</td>
                <td>{{ $report->product_name }}</td>
                <td>{{ $report->date_output }}</td>
                <td>{{ $report->time_output }}</td>
                <td>{{ $report->supplier_name }}</td>
                <td>{{ $report->quantum_out }}</td>
                <td>{{ $report->product_price }}</td>
                <td>{{ $report->total_pay }}</td>
            </tr>
        @endforeach
        <tr>
            <td><b>Tổng</b></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td><b>{{ $report_by_cabinet->sum('quantum_out') }}</b></td>
            <td></td>
            <td><b>{{ $report_by_cabinet->sum('total_pay') }}</b></td>
        </tr>
    @endforeach

    <tr>
        <td></td>
        <td></td>
        <td></td>
        <td></td>
        <td></td>
        <td></td>
        <td></td>
        <td></td>
        <td></td>
    </tr>

    <tr>
        <td></td>
        <td></td>
        <td></td>
        <td></td>
        <td></td>
        <td></td>
        <td></td>
        <td></td>
        <td></td>
    </tr>

</table>