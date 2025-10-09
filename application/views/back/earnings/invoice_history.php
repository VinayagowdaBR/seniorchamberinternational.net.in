<div id="page-head">
    <div id="page-title">
        <h1 class="page-header text-overflow">
            <i class="fa fa-file-text"></i> <?php echo translate('invoice_history'); ?>
        </h1>
    </div>
    
    <ol class="breadcrumb">
        <li><a href="<?=base_url()?>admin"><?php echo translate('home'); ?></a></li>
        <li><a href="<?=base_url()?>admin/earnings"><?php echo translate('earnings'); ?></a></li>
        <li class="active"><?php echo translate('invoice_history'); ?></li>
    </ol>
</div>

<div id="page-content">
    <div class="panel">
        <div class="panel-heading">
            <h3 class="panel-title">
                <i class="fa fa-list"></i> All Invoices (Yearly Wise)
            </h3>
        </div>
        <div class="panel-body">
            <!-- Year Filter -->
            <div class="row" style="margin-bottom: 20px;">
                <div class="col-md-3">
                    <label>Filter by Year:</label>
                    <select id="year_filter" class="form-control">
                        <option value="all">All Years</option>
                        <?php
                        $current_year = date('Y');
                        for ($y = $current_year; $y >= $current_year - 5; $y--) {
                            echo '<option value="' . $y . '">' . $y . '</option>';
                        }
                        ?>
                    </select>
                </div>
                <div class="col-md-9 text-right">
                    <button class="btn btn-success" onclick="exportInvoices()">
                        <i class="fa fa-file-excel-o"></i> Export to Excel
                    </button>
                </div>
            </div>
            
            <!-- Invoice Table -->
            <div class="table-responsive">
                <table id="invoice_history_table" class="table table-striped table-bordered">
                    <thead>
                        <tr>
                            <th width="5%">#</th>
                            <th width="12%">Invoice No</th>
                            <th width="20%">Member Name</th>
                            <th width="12%">Package</th>
                            <th width="10%">Amount</th>
                            <th width="18%">Validity Period</th>
                            <th width="10%">Date</th>
                            <th width="8%">Year</th>
                            <th width="8%">Status</th>
                            <th width="10%">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Invoice Detail Modal -->
<div class="modal fade" id="invoice_detail_modal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">&times;</button>
                <h4 class="modal-title">
                    <i class="fa fa-file-text"></i> Invoice Details
                </h4>
            </div>
            <div class="modal-body" id="invoice_detail_content">
                <!-- Content loaded via AJAX -->
            </div>
        </div>
    </div>
</div>

<script>
var invoiceTable;

$(document).ready(function() {
    // Initialize DataTable
    invoiceTable = $('#invoice_history_table').DataTable({
        "processing": true,
        "serverSide": false,
        "ajax": {
            "url": "<?php echo base_url(); ?>admin/earnings/invoice_history_data",
            "type": "POST",
            "data": function(d) {
                d.year = $('#year_filter').val();
            }
        },
        "columns": [
            { "data": "#" },
            { "data": "invoice_number" },
            { "data": "member_name" },
            { "data": "package" },
            { "data": "amount" },
            { "data": "validity" },
            { "data": "payment_date" },
            { "data": "year" },
            { "data": "status" },
            { "data": "action" }
        ],
        "order": [[6, "desc"]],
        "pageLength": 25
    });
    
    // Year filter change
    $('#year_filter').change(function() {
        invoiceTable.ajax.reload();
    });
});

function viewInvoiceDetail(payment_id) {
    $.ajax({
        url: "<?php echo base_url(); ?>admin/earnings/view_detail/" + payment_id,
        type: "GET",
        success: function(response) {
            $('#invoice_detail_content').html(response);
            $('#invoice_detail_modal').modal('show');
        },
        error: function() {
            alert('Error loading invoice details');
        }
    });
}

function exportInvoices() {
    var year = $('#year_filter').val();
    window.location.href = "<?php echo base_url(); ?>admin/earnings/export_invoices/" + year;
}
</script>

<style>
    .panel {
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    }
    .badge-success {
        background-color: #28a745;
    }
</style>
