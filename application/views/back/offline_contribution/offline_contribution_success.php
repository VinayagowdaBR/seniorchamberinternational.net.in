<div id="content-container">
    <div id="page-head">
        <div id="page-title">
            <h1 class="page-header text-overflow">
                <i class="fa fa-check-circle text-success"></i> Contribution Successful
            </h1>
        </div>
        <ol class="breadcrumb">
            <li><a href="<?= base_url('admin') ?>">Home</a></li>
            <li><a href="<?= base_url('offline_contribution/make') ?>">Offline Contribution</a></li>
            <li class="active">Contribution Success</li>
        </ol>
    </div>
    
    <div id="page-content">
        <?php if($invoice): ?>
            <div class="row">
                <div class="col-md-8 col-md-offset-2">
                    <!-- Success Message -->
                    <div class="panel panel-success">
                        <div class="panel-body text-center" style="padding: 40px;">
                            <i class="fa fa-check-circle" style="font-size: 80px; color: #28a745; margin-bottom: 20px;"></i>
                            <h2 class="text-success">Contribution Processed Successfully!</h2>
                            <p class="lead">Offline contribution has been recorded for <?= $invoice->total_members ?> member(s)</p>
                        </div>
                    </div>

                    <!-- Invoice Summary -->
                    <div class="panel panel-bordered">
                        <div class="panel-heading">
                            <h3 class="panel-title"><i class="fa fa-file-text"></i> Invoice Summary</h3>
                        </div>
                        <div class="panel-body">
                            <table class="table table-borderless">
                                <tbody>
                                    <tr>
                                        <td width="40%"><strong>Invoice Number:</strong></td>
                                        <td><span class="text-primary"><strong><?= $invoice->invoice_number ?></strong></span></td>
                                    </tr>
                                    <tr>
                                        <td><strong>Payment Date:</strong></td>
                                        <td><?= date('d M Y, h:i A', strtotime($invoice->payment_date)) ?></td>
                                    </tr>
                                    <tr>
                                        <td><strong>Plan:</strong></td>
                                        <td><span class="label label-success"><?= $invoice->plan_name ?></span></td>
                                    </tr>
                                    <tr>
                                        <td><strong>Payment Method:</strong></td>
                                        <td><?= ucfirst(str_replace('_', ' ', $invoice->payment_method)) ?></td>
                                    </tr>
                                    <?php if($invoice->transaction_id): ?>
                                    <tr>
                                        <td><strong>Transaction ID:</strong></td>
                                        <td><?= $invoice->transaction_id ?></td>
                                    </tr>
                                    <?php endif; ?>
                                    <tr>
                                        <td><strong>Total Members:</strong></td>
                                        <td><?= $invoice->total_members ?></td>
                                    </tr>
                                    <tr>
                                        <td><strong>Base Amount:</strong></td>
                                        <td>₹<?= number_format($invoice->base_amount, 2) ?></td>
                                    </tr>
                                    <tr>
                                        <td><strong>GST (<?= $invoice->gst_percentage ?>%):</strong></td>
                                        <td>₹<?= number_format($invoice->gst_amount, 2) ?></td>
                                    </tr>
                                    <tr class="active">
                                        <td><strong>Total Amount:</strong></td>
                                        <td><h4 class="text-success" style="margin: 0;">₹<?= number_format($invoice->total_amount, 2) ?></h4></td>
                                    </tr>
                                </tbody>
                            </table>

                            <?php if(!empty($members)): ?>
                                <hr>
                                <h4>Members Included (<?= count($members) ?>)</h4>
                                <div class="table-responsive">
                                    <table class="table table-sm table-bordered">
                                        <thead>
                                            <tr>
                                                <th>#</th>
                                                <th>Member ID</th>
                                                <th>Name</th>
                                                <th>Area</th>
                                                <th>Legion</th>
                                                <th>Amount</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php $i = 1; foreach($members as $member): ?>
                                                <tr>
                                                    <td><?= $i++ ?></td>
                                                    <td><?= $member['member_profile_id'] ?></td>
                                                    <td><?= $member['member_name'] ?></td>
                                                    <td><?= $member['area_name'] ?></td>
                                                    <td><?= $member['legion_name'] ?></td>
                                                    <td>₹<?= number_format($member['amount'], 2) ?></td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- Action Buttons -->
                    <div class="text-center" style="margin-bottom: 30px;">
                        <a href="<?= base_url('offline_contribution/offline_invoice_detail/' . $invoice->id) ?>" 
                           class="btn btn-primary btn-lg">
                            <i class="fa fa-eye"></i> View Invoice
                        </a>
                        <a href="<?= base_url('offline_contribution/offline_invoice_pdf/' . $invoice->id) ?>" 
                           class="btn btn-danger btn-lg" target="_blank">
                            <i class="fa fa-file-pdf-o"></i> Download PDF
                        </a>
                        <a href="<?= base_url('offline_contribution/make') ?>" 
                           class="btn btn-success btn-lg">
                            <i class="fa fa-plus"></i> New Contribution
                        </a>
                    </div>
                </div>
            </div>
        <?php else: ?>
            <div class="alert alert-danger">
                <h4>Invoice Not Found</h4>
                <p>The invoice could not be loaded.</p>
                <a href="<?= base_url('offline_contribution/offline_invoice_list') ?>" class="btn btn-primary">
                    View All Invoices
                </a>
            </div>
        <?php endif; ?>
    </div>
</div>
