<div id="content-container">
    <div id="page-head">
        <div id="page-title">
            <h1 class="page-header text-overflow">
                <i class="fa fa-file-text"></i> Offline Payment Invoices
            </h1>
            <div class="searchbox">
                <div class="pull-right">
                    <a href="<?= base_url('offline_payment/make') ?>" class="btn btn-primary btn-sm btn-labeled">
                        <span class="btn-label"><i class="fa fa-plus"></i></span>
                        New Payment
                    </a>
                </div>
            </div>
        </div>
        <ol class="breadcrumb">
            <li><a href="<?= base_url('admin') ?>">Home</a></li>
            <li><a href="<?= base_url('offline_payment/make') ?>">Offline Payment</a></li>
            <li class="active">Invoices</li>
        </ol>
    </div>
    
    <div id="page-content">
        <?php if (!empty($success_alert)): ?>
            <div class="alert alert-success alert-dismissible">
                <button class="close" data-dismiss="alert"><i class="pci-cross pci-circle"></i></button>
                <?= $success_alert ?>
            </div>
        <?php endif ?>
        
        <?php if (!empty($danger_alert)): ?>
            <div class="alert alert-danger alert-dismissible">
                <button class="close" data-dismiss="alert"><i class="pci-cross pci-circle"></i></button>
                <?= $danger_alert ?>
            </div>
        <?php endif ?>

        <div class="panel">
            <div class="panel-heading">
                <h3 class="panel-title">All Offline Payment Invoices</h3>
            </div>
            <div class="panel-body">
                <div class="table-responsive">
                    <table class="table table-striped table-bordered table-hover" id="offline_invoice_table">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Invoice Number</th>
                                <th>Payment Date</th>
                                <th>Plan</th>
                                <th>Members</th>
                                <th>Amount</th>
                                <th>Method</th>
                                <th>Status</th>
                                <?php if($this->session->userdata('admin_id') == 1): ?>
                                    <th>Created By</th>
                                <?php endif; ?>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            if(!empty($invoices)){
                                $i = 1;
                                foreach($invoices as $inv): 
                            ?>
                            <tr>
                                <td><?= $i++ ?></td>
                                <td>
                                    <strong class="text-primary"><?= $inv->invoice_number ?></strong>
                                    <?php if(!empty($inv->transaction_id)): ?>
                                        <br><small class="text-muted">Ref: <?= $inv->transaction_id ?></small>
                                    <?php endif; ?>
                                </td>
                                <td><?= date('d M Y', strtotime($inv->payment_date)) ?><br>
                                    <small class="text-muted"><?= date('h:i A', strtotime($inv->payment_date)) ?></small>
                                </td>
                                <td><span class="label label-success"><?= $inv->plan_name ?></span></td>
                                <td class="text-center"><?= $inv->total_members ?></td>
                                <td><strong>₹<?= number_format($inv->total_amount, 2) ?></strong></td>
                                <td><?= ucfirst(str_replace('_', ' ', $inv->payment_method)) ?></td>
                                <td>
                                    <?php if($inv->payment_status == 'completed'): ?>
                                        <span class="label label-success">Completed</span>
                                    <?php else: ?>
                                        <span class="label label-warning"><?= ucfirst($inv->payment_status) ?></span>
                                    <?php endif; ?>
                                </td>
                                <?php if($this->session->userdata('admin_id') == 1): ?>
                                    <td><?= isset($inv->admin_name) ? $inv->admin_name : 'N/A' ?></td>
                                <?php endif; ?>
                                <td>
                                    <div class="btn-group">
                                        <a href="<?= base_url('offline_payment/offline_invoice_detail/' . $inv->id) ?>" 
                                           class="btn btn-sm btn-info" title="View Details">
                                            <i class="fa fa-eye"></i>
                                        </a>
                                        <a href="<?= base_url('offline_payment/offline_invoice_pdf/' . $inv->id) ?>" 
                                           class="btn btn-sm btn-danger" target="_blank" title="Download PDF">
                                            <i class="fa fa-file-pdf-o"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                            <?php 
                                endforeach; 
                            } else { 
                            ?>
                                <tr>
                                    <td colspan="<?= $this->session->userdata('admin_id') == 1 ? '10' : '9' ?>" class="text-center">
                                        <div class="alert alert-info" style="margin: 20px;">
                                            <i class="fa fa-info-circle"></i> No invoices found
                                        </div>
                                    </td>
                                </tr>
                            <?php } ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
$(document).ready(function(){
    $('#offline_invoice_table').DataTable({
        "pageLength": 25,
        "order": [[0, "desc"]],
        "columnDefs": [
            { "orderable": false, "targets": <?= $this->session->userdata('admin_id') == 1 ? '9' : '8' ?> }
        ]
    });
});
</script>
