<div class="container-fluid py-2">

    <!-- Member & Plan Info -->
    <div class="card shadow-sm mb-3 border-0">
        <div class="card-header bg-primary text-white py-2">
            <h5 class="mb-0"><i class="fa fa-user-circle"></i> Member & Plan Information</h5>
        </div>
        <div class="card-body p-3">
            <div class="table-responsive">
                <table class="table table-sm table-bordered mb-0">
                    <tr>
                        <th class="bg-light w-25">Member</th>
                        <td><?= htmlspecialchars($member->first_name . ' ' . $member->last_name) ?></td>
                    </tr>
                    <tr>
                        <th class="bg-light">Plan</th>
                        <td><?= htmlspecialchars($plan->plan_name) ?></td>
                    </tr>
                    <tr>
                        <th class="bg-light">Payment Type</th>
                        <td><span class="badge bg-info text-dark px-2 py-1"><?= htmlspecialchars($payment->payment_type) ?></span></td>
                    </tr>
                    <tr>
                        <th class="bg-light">Amount</th>
                        <td><strong class="text-success">₹ <?= number_format($payment->amount, 2) ?></strong></td>
                    </tr>
                    <tr>
                        <th class="bg-light">Status</th>
                        <td>
                            <?php if ($payment->payment_status === 'paid'): ?>
                                <span class="badge bg-success">Paid</span>
                            <?php elseif ($payment->payment_status === 'pending'): ?>
                                <span class="badge bg-warning text-dark">Pending</span>
                            <?php else: ?>
                                <span class="badge bg-secondary"><?= ucfirst($payment->payment_status) ?></span>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <tr>
                        <th class="bg-light">Invoice No</th>
                        <td><?= htmlspecialchars($payment->invoice_number) ?></td>
                    </tr>
                    <tr>
                        <th class="bg-light">Transaction ID</th>
                        <td><?= htmlspecialchars($payment->payment_code) ?></td>
                    </tr>
                </table>
            </div>
        </div>
    </div>

    <!-- Payment History -->
    <div class="card shadow-sm border-0">
        <div class="card-header bg-secondary text-white py-2">
            <h5 class="mb-0"><i class="fa fa-history"></i> Payment History</h5>
        </div>
        <div class="card-body p-3">
            <div class="table-responsive">
                <table class="table table-striped table-hover align-middle mb-0">
                    <thead class="table-dark">
                        <tr>
                            <th>Date</th>
                            <th>Amount (₹)</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($payment_history)): ?>
                            <?php foreach ($payment_history as $h): ?>
                                <tr>
                                    <td><?= date('Y-m-d H:i', $h->purchase_datetime) ?></td>
                                    <td><?= number_format($h->amount, 2) ?></td>
                                    <td>
                                        <?php if ($h->payment_status === 'paid'): ?>
                                            <span class="badge bg-success">Paid</span>
                                        <?php elseif ($h->payment_status === 'pending'): ?>
                                            <span class="badge bg-warning text-dark">Pending</span>
                                        <?php else: ?>
                                            <span class="badge bg-secondary"><?= ucfirst($h->payment_status) ?></span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="3" class="text-center text-muted py-3">No payment history available.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

</div>
