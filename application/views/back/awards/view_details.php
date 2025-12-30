<?php
$form_data = array();
if ($entry['award_for'] == 'legion') {
    $form_data = json_decode($entry['legion_form_json'], true);
} else {
    $form_data = json_decode($entry['individual_form_json'], true);
}
?>

<div class="row">
    <div class="col-md-12">
        <div class="panel">
            <div class="panel-heading">
                <h3 class="panel-title">Award Details: <?= $entry['category']; ?> (<?= $entry['year']; ?>)</h3>
                <div class="pull-right" style="margin-top: -25px; margin-right: 15px;">
                    <a href="<?= base_url('admin/award/report'); ?>" class="btn btn-primary btn-sm"> <i class="fa fa-arrow-left"></i> Back to Report</a>
                </div>
            </div>
            <div class="panel-body">
                
                <div class="row">
                    <div class="col-md-6">
                        <h4>Applicant Info</h4>
                        <table class="table table-bordered">
                            <tr><th>Award Type</th><td><?= ucfirst($entry['award_for']); ?></td></tr>
                            <tr><th>Legion Name</th><td><?= $entry['legion_name']; ?></td></tr>
                            <?php if($entry['award_for'] == 'individual'): ?>
                                <tr><th>Nominee Name</th><td><?= $entry['nominee_name']; ?></td></tr>
                                <tr><th>Member ID</th><td><?= $entry['member_id']; ?></td></tr>
                            <?php endif; ?>
                            <tr><th>Area ID</th><td><?= $entry['area_id']; ?></td></tr>
                            <tr><th>Status</th><td><span class="label label-success"><?= ucfirst($entry['status']); ?></span></td></tr>
                            <tr><th>Total Points</th><td><b><?= (int)$entry['total_points']; ?></b></td></tr>
                            <tr><th>Submission Date</th><td><?= date('d-M-Y H:i', strtotime($entry['created_at'])); ?></td></tr>
                        </table>
                    </div>
                </div>

                <hr>
                
                <h4>Form Data</h4>
                <table class="table table-striped">
                    <?php if (!empty($form_data)): ?>
                        <?php foreach ($form_data as $key => $value): ?>
                            <tr>
                                <th width="30%"><?= ucwords(str_replace('_', ' ', $key)); ?></th>
                                <td>
                                    <?php 
                                        // Check if value is a file path (contains 'uploads/')
                                        if (is_string($value) && strpos($value, 'uploads/') !== false): 
                                    ?>
                                        <?php if (preg_match('/\.(jpg|jpeg|png|gif)$/i', $value)): ?>
                                            <div style="margin-bottom:5px;">
                                                <img src="<?= base_url($value); ?>" style="max-width: 200px; border:1px solid #ddd; padding:2px;">
                                            </div>
                                            <a href="<?= base_url($value); ?>" target="_blank" class="btn btn-xs btn-info">View Full Image</a>
                                            <a href="<?= base_url($value); ?>" download class="btn btn-xs btn-default">Download</a>
                                        <?php else: ?>
                                            <a href="<?= base_url($value); ?>" target="_blank" class="btn btn-success"><i class="fa fa-download"></i> Download Document</a>
                                        <?php endif; ?>
                                    <?php else: ?>
                                        <?= $value; ?>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr><td>No additional form data found.</td></tr>
                    <?php endif; ?>
                </table>

            </div>
        </div>
    </div>
</div>
