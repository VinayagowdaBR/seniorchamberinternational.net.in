<?php
if($invoice):
    $invoice_number = $invoice->invoice_number;
    $payment_date = date('d M Y, h:i A', strtotime($invoice->payment_date));
    $transaction_id = $invoice->transaction_id;
    $total_amount = $invoice->total_amount;
    $base_amount = $invoice->base_amount;
    $gst_amount = $invoice->gst_amount;
    $gst_percentage = $invoice->gst_percentage;
    $total_members = $invoice->total_members;
    $plan_name = $invoice->plan_name;
    $payment_method = ucfirst(str_replace('_', ' ', $invoice->payment_method));
    $paid_by_name = $paid_by ? $paid_by->name : 'Unknown';
    $paid_by_email = $paid_by ? $paid_by->email : '';
?>

<div id="content-container">
    <div id="page-head">
        <div id="page-title">
            <h1 class="page-header text-overflow">
                <i class="fa fa-file-text"></i> Invoice Details
            </h1>
            <div class="searchbox">
                <div class="pull-right">
                    <a href="<?= base_url('offline_payment/offline_invoice_list') ?>" class="btn btn-default btn-sm btn-labeled">
                        <span class="btn-label"><i class="fa fa-arrow-left"></i></span>
                        Back to List
                    </a>
                    <a href="<?= base_url('offline_payment/offline_invoice_pdf/' . $invoice->id) ?>" 
                       class="btn btn-danger btn-sm btn-labeled" target="_blank">
                        <span class="btn-label"><i class="fa fa-file-pdf-o"></i></span>
                        Download PDF
                    </a>
                    <button onclick="window.print()" class="btn btn-info btn-sm btn-labeled">
                        <span class="btn-label"><i class="fa fa-print"></i></span>
                        Print
                    </button>
                </div>
            </div>
        </div>
        <ol class="breadcrumb">
            <li><a href="<?= base_url('admin') ?>">Home</a></li>
            <li><a href="<?= base_url('offline_payment/make') ?>">Offline Payment</a></li>
            <li><a href="<?= base_url('offline_payment/offline_invoice_list') ?>">Invoices</a></li>
            <li class="active">Invoice Detail</li>
        </ol>
    </div>
    
    <div id="page-content">
        <div class="panel" id="invoice_print_area">
            <div class="panel-body" style="padding: 40px;">
                <!-- Header -->
                <div class="row">
                    <div class="col-xs-6">
                        <h2 style="margin-top: 0;">
                            <?= $this->Crud_model->get_type_name_by_id('general_settings', 1, 'value') ?>
                        </h2>
                        <p>
                            <?= $this->Crud_model->get_type_name_by_id('general_settings', 2, 'value') ?><br>
                            Phone: <?= $this->Crud_model->get_type_name_by_id('general_settings', 4, 'value') ?>
                        </p>
                    </div>
                    <div class="col-xs-6 text-right">
                        <h3 class="text-primary" style="margin-top: 0;">INVOICE</h3>
                        <p>
                            <strong>Invoice #:</strong> <?= $invoice_number ?><br>
                            <strong>Date:</strong> <?= $payment_date ?><br>
                            <strong>Status:</strong> <span class="label label-success">PAID</span>
                        </p>
                    </div>
                </div>

                <hr style="border-top: 2px solid #ddd; margin: 30px 0;">

                <!-- Bill To & Payment Info -->
                <div class="row">
                    <div class="col-xs-6">
                        <h4 class="text-info">Bill To:</h4>
                        <address>
                            <strong><?= $paid_by_name ?></strong><br>
                            <?php if($paid_by_email): ?>
                                Email: <?= $paid_by_email ?><br>
                            <?php endif; ?>
                            Role: Admin
                        </address>
                    </div>
                    <div class="col-xs-6 text-right">
                        <h4 class="text-info">Payment Information:</h4>
                        <p>
                            <strong>Payment Method:</strong> <?= $payment_method ?><br>
                            <?php if($transaction_id): ?>
                                <strong>Transaction ID:</strong> <?= $transaction_id ?><br>
                            <?php endif; ?>
                            <strong>Payment Status:</strong> <span class="label label-success">Completed</span>
                        </p>
                    </div>
                </div>

                <hr style="margin: 30px 0;">

                <!-- Plan Details -->
                <div class="row">
                    <div class="col-xs-12">
                        <h4 class="text-info">Plan Details:</h4>
                        <div class="well" style="background: #f9f9f9;">
                            <div class="row">
                                <div class="col-sm-4">
                                    <strong>Plan Name:</strong><br>
                                    <span class="label label-success label-lg"><?= $plan_name ?></span>
                                </div>
                                <div class="col-sm-4">
                                    <strong>Total Members:</strong><br>
                                    <?= $total_members ?>
                                </div>
                                <div class="col-sm-4">
                                    <strong>Rate per Member:</strong><br>
                                    ₹<?= number_format($base_amount / $total_members, 2) ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Members List -->
                <?php if(!empty($members)): ?>
                <div class="row">
                    <div class="col-xs-12">
                        <h4 class="text-info">Members Included (<?= count($members) ?>):</h4>
                        <div class="table-responsive">
                            <table class="table table-bordered table-hover">
                                <thead class="table-header-bg">
                                    <tr style="background: #f5f5f5;">
                                        <th width="50">#</th>
                                        <th>Member ID</th>
                                        <th>Member Name</th>
                                        <th>Email</th>
                                        <th>Area</th>
                                        <th>Legion</th>
                                        <th class="text-right">Amount</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php 
                                    $i = 1;
                                    foreach($members as $member): 
                                    ?>
                                    <tr>
                                        <td><?= $i++ ?></td>
                                        <td><strong><?= $member['member_profile_id'] ?></strong></td>
                                        <td><?= $member['member_name'] ?></td>
                                        <td><?= $member['member_email'] ?></td>
                                        <td><span class="label label-info"><?= $member['area_name'] ?></span></td>
                                        <td><span class="label label-primary"><?= $member['legion_name'] ?></span></td>
                                        <td class="text-right">₹<?= number_format($member['amount'], 2) ?></td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                <?php endif; ?>

                <hr style="margin: 30px 0;">

                <!-- Amount Summary -->
                <div class="row">
                    <div class="col-xs-6 col-xs-offset-6">
                        <table class="table" style="margin-bottom: 0;">
                            <tbody>
                                <tr>
                                    <td><strong>Subtotal:</strong></td>
                                    <td class="text-right">₹<?= number_format($base_amount, 2) ?></td>
                                </tr>
                                <tr>
                                    <td><strong>GST (<?= $gst_percentage ?>%):</strong></td>
                                    <td class="text-right">₹<?= number_format($gst_amount, 2) ?></td>
                                </tr>
                                <tr class="active">
                                    <td><h4 style="margin: 0;"><strong>Total Amount:</strong></h4></td>
                                    <td class="text-right">
                                        <h4 class="text-success" style="margin: 0;">
                                            <strong>₹<?= number_format($total_amount, 2) ?></strong>
                                        </h4>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <?php if(!empty($invoice->notes)): ?>
                <hr style="margin: 30px 0;">
                <div class="row">
                    <div class="col-xs-12">
                        <h4 class="text-info">Notes / Remarks:</h4>
                        <div class="well" style="background: #fffbea; border-color: #f0e68c;">
                            <?= nl2br(htmlspecialchars($invoice->notes)) ?>
                        </div>
                    </div>
                </div>
                <?php endif; ?>

                <hr style="margin: 30px 0;">

                <!-- Footer -->
                <div class="row">
                    <div class="col-xs-12 text-center">
                        <p class="text-muted">
                            <small>
                                This is a computer-generated invoice and does not require a physical signature.<br>
                                Generated on <?= date('d M Y, h:i A') ?>
                            </small>
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style media="print">
    body * {
        visibility: hidden;
    }
    #invoice_print_area, #invoice_print_area * {
        visibility: visible;
    }
    #invoice_print_area {
        position: absolute;
        left: 0;
        top: 0;
        width: 100%;
    }
    .btn, .breadcrumb, #page-head {
        display: none !important;
    }
    .label {
        border: 1px solid #333;
        padding: 3px 6px;
    }
</style>

<?php else: ?>
    <div class="panel">
        <div class="panel-body">
            <div class="alert alert-danger">
                <h4>Invoice Not Found</h4>
                <p>The requested invoice could not be found.</p>
                <a href="<?= base_url('offline_payment/offline_invoice_list') ?>" class="btn btn-primary">
                    Back to Invoice List
                </a>
            </div>
        </div>
    </div>
<?php endif; ?>
