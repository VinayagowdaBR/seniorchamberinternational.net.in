<!-- PAGE HEADER -->
<div id="page-head">
    <div id="page-title">
        <h1 class="page-header text-overflow text-success">
            <i class="fa fa-check-circle"></i> <?php echo translate('bulk_payment_success'); ?>
        </h1>
    </div>

    <ol class="breadcrumb">
        <li><a href="<?= base_url() ?>admin"><?php echo translate('home'); ?></a></li>
        <li><a href="<?= base_url() ?>admin/earnings"><?php echo translate('earnings'); ?></a></li>
        <li class="active"><?php echo translate('payment_success'); ?></li>
    </ol>
</div>

<!-- PAGE CONTENT -->
<div id="page-content">
    <div class="row">
        <div class="col-md-10 col-md-offset-1">

            <!-- SUCCESS MESSAGE -->
            <div class="alert alert-success alert-dismissible fade in shadow-sm" style="padding: 20px; border-left: 5px solid #28a745;">
                <button type="button" class="close" data-dismiss="alert">&times;</button>
                <h4 class="text-success"><i class="fa fa-check-circle"></i> Payment Successful!</h4>
                <p>Bulk payment has been processed successfully. Invoices have been generated for all members.</p>
            </div>

            <!-- INVOICE SUMMARY -->
            <div class="panel panel-success shadow-sm">
                <div class="panel-heading text-center">
                    <h3 class="panel-title text-white">
                        <i class="fa fa-file-text"></i> Generated Invoices (<?php echo count($invoices); ?> Members)
                    </h3>
                </div>

                <div class="panel-body">
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover text-center">
                            <thead style="background-color: #28a745; color: #fff;">
                                <tr>
                                    <th>#</th>
                                    <th>Invoice Number</th>
                                    <th>Member Name</th>
                                    <th>Amount</th>
                                    <th>Status</th>
                                    <th>Action</th>
                                </tr>
                            </thead>

                            <tbody>
                                <?php 
                                $sl = 1;
                                $total = 0;
                                foreach ($invoices as $invoice): 
                                    $total += $invoice['amount'];
                                ?>
                                <tr>
                                    <td><?php echo $sl++; ?></td>
                                    <td><strong><?php echo $invoice['invoice_number']; ?></strong></td>
                                    <td><?php echo $invoice['member_name']; ?></td>
                                    <td class="text-success">
                                        <strong><?php echo currency('', 'def') . number_format($invoice['amount'], 2); ?></strong>
                                    </td>
                                    <td>
                                        <span class="badge badge-success" style="background-color: #28a745;">
                                            <i class="fa fa-check"></i> Generated
                                        </span>
                                    </td>
                                    <td>
                                        <a href="<?php echo base_url('admin/earnings/download_invoice/' . $invoice['payment_id']); ?>" 
                                           class="btn btn-primary btn-sm" target="_blank">
                                            <i class="fa fa-download"></i> Download
                                        </a>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>

                            <tfoot>
                                <tr class="success" style="background-color: #e8f5e9;">
                                    <td colspan="3" class="text-right"><strong>Total Amount Paid:</strong></td>
                                    <td colspan="3">
                                        <h4 class="text-success m-0">
                                            <strong><?php echo currency('', 'def') . number_format($total, 2); ?></strong>
                                        </h4>
                                    </td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>

            <!-- ACTION BUTTONS -->
            <div class="text-center" style="margin-top: 30px;">
                <a href="<?php echo base_url('admin/earnings'); ?>" class="btn btn-primary btn-lg" style="margin-right: 10px;">
                    <i class="fa fa-arrow-left"></i> Back to Earnings
                </a>
                <button onclick="downloadAllInvoices()" class="btn btn-success btn-lg">
                    <i class="fa fa-download"></i> Download All Invoices
                </button>
            </div>

        </div>
    </div>
</div>

<!-- CUSTOM STYLES -->
<style>
    /* ✅ Sidebar content alignment fix */
    #content-container,
    #page-content {
        margin-left: 250px; /* Adjust based on sidebar width */
        padding: 20px;
        transition: all 0.3s ease;
    }

    /* Responsive fix for smaller screens */
    @media (max-width: 992px) {
        #content-container,
        #page-content {
            margin-left: 0;
            padding: 15px;
        }
    }

    /* Panel Styling */
    .panel-success {
        border-color: #28a745;
        border-radius: 8px;
        box-shadow: 0 3px 8px rgba(0,0,0,0.1);
    }
    .panel-success > .panel-heading {
        background-color: #28a745 !important;
        color: white;
        border-radius: 8px 8px 0 0;
        padding: 12px;
        font-weight: 600;
        letter-spacing: 0.5px;
    }

    /* Table Styling */
    .table th, .table td {
        vertical-align: middle !important;
    }

    /* Buttons */
    .btn-lg {
        border-radius: 6px;
        padding: 10px 25px;
    }

    /* Alerts */
    .alert-success {
        border-left: 5px solid #28a745;
        background-color: #e8f5e9;
        color: #155724;
        font-size: 15px;
    }

    /* Shadow utility */
    .shadow-sm {
        box-shadow: 0 2px 6px rgba(0,0,0,0.08);
    }
</style>

<!-- JAVASCRIPT -->
<script>
    // Download all invoices in new tabs
    function downloadAllInvoices() {
        <?php foreach($invoices as $invoice): ?>
            window.open('<?php echo base_url("admin/earnings/download_invoice/" . $invoice["payment_id"]); ?>', '_blank');
        <?php endforeach; ?>
    }

    // Auto-hide success alert after few seconds
    $(document).ready(function() {
        setTimeout(function() {
            $('.alert-success').fadeOut('slow');
        }, 5000);
    });
</script>
